<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\User;
use Auth;


class Menu extends Model
{
    protected $table = 'permissions';

    public function getChildren($data, $line)
    {
        $children = [];
        foreach ($data as $line1) {
            if ($line['id'] == $line1['parent']) {
                $children = array_merge($children, [ array_merge($line1, ['submenu' => $this->getChildren($data, $line1) ]) ]);
            }
        }
        return $children;
    }

    public function optionsMenu($core_app_id)
    {
        return $this->where('enabled', 1)
            ->where('core_app_id', $core_app_id)
            ->orderby('parent')
            ->orderby('orden')
            ->orderby('name')
            ->get()
            ->toArray();
        
    }
    
    public static function menus($core_app_id)
    {
        $menus = new Menu();
        $data = $menus->optionsMenu($core_app_id);
        $menuAll = [];
        foreach ($data as $line) {
            $item = [ array_merge($line, ['submenu' => $menus->getChildren($data, $line) ]) ];
            $menuAll = array_merge($menuAll, $item);
        }
        return $menus->menuAll = $menuAll;
    }
}

    
