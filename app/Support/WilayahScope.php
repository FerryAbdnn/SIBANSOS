<?php
namespace App\Support;
use App\Models\Wilayah;

class WilayahScope
{
    public static function ids(?int $root): array
    {
        if (!$root) {
            return [];
        }

        $ids = [$root];
        $frontier = [$root];

        while (!empty($frontier)) {
            $children = Wilayah::whereIn(
                'id_induk',
                $frontier
            )->pluck('id_wilayah')->all();

            $children = array_values(
                array_diff($children, $ids)
            );

            $ids = array_merge($ids, $children);
            $frontier = $children;
        }

        return $ids;
    }
}