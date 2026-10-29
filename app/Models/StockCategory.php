<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Stock categories sit in a tree: Stock Group -> Category -> Sub-category -> ...
 * A category is directly under a stock group (parent_id null), or under another category
 * (a sub-category), and then it is in the same stock group as that category.
 */
class StockCategory extends Model
{
    protected $guarded = [];

    // The company this stock category belongs to
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // The stock group this category belongs to (for a sub-category: the same group as its parent)
    public function stockGroup()
    {
        return $this->belongsTo(StockGroup::class);
    }

    // The stock category this one sits under (null when it is directly under its stock group)
    public function parent()
    {
        return $this->belongsTo(StockCategory::class, 'parent_id');
    }

    // The sub-categories that sit under this one
    public function children()
    {
        return $this->hasMany(StockCategory::class, 'parent_id');
    }

    // The items in this stock category
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    // Ids of every sub-category below this one, at any depth
    public function descendantIds()
    {
        $categories = self::where('company_id', $this->company_id)->get(['id', 'parent_id']);

        $ids = [];
        $found = [$this->id];

        while ($found) {
            $found = $categories->whereIn('parent_id', $found)->pluck('id')->all();
            $ids = array_merge($ids, $found);
        }

        return $ids;
    }

    // What the "Under" dropdown has selected for this category: "category:5" or "group:2"
    public function underValue()
    {
        if ($this->parent_id) {
            return 'category:' . $this->parent_id;
        }

        return $this->stock_group_id ? 'group:' . $this->stock_group_id : '';
    }

    /**
     * Options of the "Under" dropdown, in tree order: each stock group, the categories in it
     * (and their sub-categories), then its sub-groups. depth is for indenting.
     * $except: the category being edited. It and its sub-categories are left out (it can't go under them).
     * Categories without a stock group (made before categories had one) are left out until they get one.
     */
    public static function underOptions($companyId, $except = null)
    {
        $groups = StockGroup::where('company_id', $companyId)->orderBy('name')->get();
        $categories = self::where('company_id', $companyId)->whereNotNull('stock_group_id')->orderBy('name')->get();

        if ($except) {
            $categories = $categories->whereNotIn('id', array_merge([$except->id], $except->descendantIds()));
        }

        $options = [];

        $addCategories = function ($groupId, $parentId, $depth) use (&$addCategories, &$options, $categories) {
            foreach ($categories->where('stock_group_id', $groupId)->where('parent_id', $parentId) as $category) {
                $options[] = ['value' => 'category:' . $category->id, 'name' => $category->name, 'depth' => $depth, 'type' => 'category'];
                $addCategories($groupId, $category->id, $depth + 1);
            }
        };

        $addGroups = function ($parentId, $depth) use (&$addGroups, &$options, $addCategories, $groups) {
            foreach ($groups->where('parent_id', $parentId) as $group) {
                $options[] = ['value' => 'group:' . $group->id, 'name' => $group->name, 'depth' => $depth, 'type' => 'group'];
                $addCategories($group->id, null, $depth + 1);
                $addGroups($group->id, $depth + 1);
            }
        };

        $addGroups(null, 0);

        return $options;
    }
}
