<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Group;
use App\Models\Page;
use App\Models\Storage;
use App\PageType;
use App\StorageProvider;
use Illuminate\Database\Seeder;

/**
 * 初始化系统默认数据
 */
class InitializeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 填充系统默认组和储存
        /** @var Group $group */
        $group = Group::create([
            'name' => '系统默认组',
            'intro' => '这是系统默认角色组',
            'options' => Group::getDefaultOptions(),
            'is_guest' => true,
            'is_default' => true,
        ]);

        // 填充本地储存驱动
        $storage = Storage::create([
            'name' => '本地储存',
            'intro' => '这是本地储存驱动',
            'prefix' => 'uploads',
            'provider' => StorageProvider::Local,
            'options' => Driver::getLocalStorageDefaultOptions(),
        ]);

        $group->storages()->attach($storage->id);

        // 页面
        Page::create([
            'type' => PageType::Internal,
            'name' => '关于我们',
            'icon' => 'fa-users',
            'title' => '关于我们',
            'content' => '关于我们',
            'slug' => 'about',
            'url' => '',
            'is_show' => true,
        ]);
    }
}
