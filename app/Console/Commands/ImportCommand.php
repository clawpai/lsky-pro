<?php

namespace App\Console\Commands;

use App\DriverType;
use App\Facades\AppService;
use App\FeedbackType;
use App\MailProvider;
use App\Models\Album;
use App\Models\Driver;
use App\Models\Feedback;
use App\Models\Group;
use App\Models\GroupStorage;
use App\Models\Notice;
use App\Models\OAuth;
use App\Models\Order;
use App\Models\Page;
use App\Models\Photo;
use App\Models\Plan;
use App\Models\Share;
use App\Models\Storage;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Violation;
use App\OrderStatus;
use App\OrderType;
use App\PageType;
use App\PaymentProvider;
use App\ReportStatus;
use App\ScanProvider;
use App\Settings\AppSettings;
use App\Settings\SiteSettings;
use App\ShareType;
use App\SocialiteProvider;
use App\StorageProvider;
use App\TicketLevel;
use App\TicketStatus;
use App\UserCapacityFrom;
use App\UserGroupFrom;
use App\UserStatus;
use App\ViolationStatus;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\LaravelSettings\Settings;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Throwable;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\password;
use function Laravel\Prompts\pause;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

/**
 * 导入旧版本(v1.7.x)数据
 */
#[AsCommand(name: 'app:import')]
class ImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Example Import data of an earlier version (v1.7.x)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        info("你正在执行导入旧版本数据操作，此操作仅导入旧版本(仅支持 1.7 或 1.7.1 版本)数据，不包含本地、第三方储存中的图片资源，该请手动迁移。\n命令执行过程中请不要中断或重启服务器，导入前请确保当前版本(2.x)已经安装成功，且没有创建任何数据，否则会导致数据混乱。");
        pause('确认无误后，请按回车键继续。');

        if (! AppService::isInstalled()) {
            error('请先安装 Lsky Pro+ - 2x.nz特供离线版 v2.x 版本');
            return CommandAlias::FAILURE;
        }

        info('请输入旧版本数据库连接信息');

        try {
            $config = [
                'driver' => $this->getDbConnection(),
            ];

            if ($config['driver'] !== 'sqlite') {
                $config = array_merge($config, [
                    'host' => $this->getDbHost(),
                    'port' => $this->getDbPort(),
                    'database' => $this->getDbDatabase(),
                    'username' => $this->getDbUsername(),
                    'password' => $this->getDbPassword(),
                ]);
            } else {
                $config = array_merge($config, [
                    'database' => $this->getSqliteFilepath(),
                ]);
            }

            // 连接旧版本数据库
            $connection = 'old';
            Config::set("database.connections.{$connection}", array_merge(Config::get("database.connections.{$config['driver']}"), array_filter($config)));

            DB::purge($connection);
            DB::reconnect($connection);

            try {
                DB::connection($connection)->getPdo();
            } catch (Throwable $e) {
                throw new Exception("旧版本数据库连接失败: {$e->getMessage()}");
            }

            info('数据迁移中，请不要关闭窗口或重启服务器...');

            ini_set('memory_limit', '1G');

            // 迁移配置
            $configs = DB::connection($connection)->table('configs')->get()->pluck('value', 'name')->toArray();

            $mail = json_decode(data_get($configs, 'mail', '[]'), true) ?: []; // 邮件配置
            $background = json_decode(data_get($configs, 'background', '[]'), true) ?: []; // 背景图配置
            $socialite = json_decode(data_get($configs, 'socialite', '[]'), true) ?: []; // 社会化登录配置
            $payment = json_decode(data_get($configs, 'payment', '[]'), true) ?: []; // 支付配置

            DB::beginTransaction();
            Model::unguard();

            // 清空表
            DB::table('group_storage')->delete();
            DB::table('user_groups')->delete();
            DB::table('user_capacities')->delete();
            DB::table('group_driver')->delete();
            DB::table('groups')->delete();
            DB::table('users')->delete();
            DB::table('pages')->delete();
            DB::table('feedbacks')->delete();
            DB::table('reports')->delete();
            DB::table('notices')->delete();
            DB::table('violations')->delete();

            // 创建邮件驱动
            /** @var Driver $mailDriver */
            $mailDriver = Driver::create([
                'type' => DriverType::Mail,
                'name' => 'SMTP',
                'options' => array_merge(Arr::only(data_get($mail, 'mailers.smtp'), ['host', 'port', 'username', 'password', 'encryption', 'timeout']), [
                    'provider' => MailProvider::Smtp->value,
                ]),
            ]);

            // 迁移支付驱动
            foreach ($payment as $key => $item) {
                if ($key === 'epay') {
                    continue;
                }

                if ($key === 'paypal') {
                    Driver::create([
                        'type' => DriverType::Payment,
                        'name' => $key,
                        'options' => array_merge([
                            'provider' => PaymentProvider::from($key)->value,
                        ], Arr::except($item, ['enable']), [
                            'mode' => 'live',
                            'client_secret' => data_get($item, 'secret'),
                        ]),
                    ]);
                } else {
                    Driver::create([
                        'type' => DriverType::Payment,
                        'name' => $key,
                        'options' => array_merge([
                            'provider' => PaymentProvider::from($key)->value,
                        ], Arr::except($item, ['enable'])),
                    ]);
                }
            }

            // 迁移社会化登录驱动
            $socialiteDrivers = [];
            foreach ($socialite as $key => $item) {
                /** @var Driver $socialiteDriver */
                $socialiteDriver = Driver::create([
                    'type' => DriverType::Socialite,
                    'name' => $key,
                    'options' => array_merge([
                        'provider' => SocialiteProvider::from($key)->value,
                    ], Arr::only($item, ['client_id', 'client_secret'])),
                ]);

                $socialiteDrivers[$key] = $socialiteDriver->id;
            }

            $configData = [
                ['app.name' => data_get($configs, 'app_name')],
                ['app.icp_no' => data_get($configs, 'icp_no')],
                ['app.enable_registration' => (int)data_get($configs, 'is_enable_registration')],
                ['app.guest_upload' => (int)data_get($configs, 'is_allow_guest_upload')],
                ['app.mail_from_address' => data_get($mail, 'from.address')],
                ['app.mail_from_name' => data_get($mail, 'from.name')],
                ['site.title' => data_get($configs, 'app_name')],
                ['site.subtitle' => data_get($configs, 'site_subtitle')],
                ['site.homepage_title' => data_get($configs, 'homepage_title')],
                ['site.homepage_description' => data_get($configs, 'homepage_description')],
                ['site.notice' => data_get($configs, 'site_notice')],
                ['site.homepage_background_image_url' => data_get($background, 'home.url')],
                ['site.homepage_background_images' => json_encode(Arr::pluck(data_get($background, 'home.images', []), 'src'))],
                ['site.auth_page_background_image_url' => data_get($background, 'auth.url')],
                ['site.auth_page_background_images' => json_encode(Arr::pluck(data_get($background, 'auth.images', []), 'src'))],
            ];

            // 迁移系统配置
            $settings = [app(AppSettings::class), app(SiteSettings::class)];
            /** @var Settings $setting */
            foreach ($settings as $setting) {
                foreach (Arr::undot($configData) as $group => $item) {
                    if ($group === $setting::group()) {
                        $setting->fill($item);
                    }
                }
                $setting->save();
            }

            // 迁移角色组
            foreach (DB::connection($connection)->table('groups')->get()->toArray() as $group) {
                $groupConfigs = json_decode($group->configs, true);
                $groupDefaultOptions = Group::getDefaultOptions();

                /** @var Group $tempGroup */
                $tempGroup = Group::create([
                    'id' => $group->id,
                    'name' => $group->name,
                    'is_default' => $group->is_default,
                    'is_guest' => $group->is_guest,
                    'options' => array_merge($groupDefaultOptions, [
                        'max_upload_size' => data_get($groupConfigs, 'maximum_file_size', $groupDefaultOptions['max_upload_size']),
                        'limit_concurrent_upload' => data_get($groupConfigs, 'concurrent_upload_num', $groupDefaultOptions['limit_concurrent_upload']),
                        'limit_per_minute' => data_get($groupConfigs, 'limit_per_minute', $groupDefaultOptions['limit_per_minute']),
                        'limit_per_hour' => data_get($groupConfigs, 'limit_per_hour', $groupDefaultOptions['limit_per_hour']),
                        'limit_per_day' => data_get($groupConfigs, 'limit_per_day', $groupDefaultOptions['limit_per_day']),
                        'limit_per_week' => data_get($groupConfigs, 'limit_per_week', $groupDefaultOptions['limit_per_week']),
                        'limit_per_month' => data_get($groupConfigs, 'limit_per_month', $groupDefaultOptions['limit_per_month']),
                    ])
                ]);

                // 创建鉴黄驱动
                $aliyunVersion = data_get($groupConfigs, 'scan_configs.drivers.aliyun.version', 1);

                if ($aliyunVersion == 1) {
                    $aliyunV1Driver = Driver::create([
                        'type' => DriverType::Scan,
                        'name' => "{$group->name} 组图片安全 - 阿里云 V1",
                        'options' => array_merge([
                            'provider' => ScanProvider::AliyunV1->value
                        ], Arr::only(data_get($groupConfigs, 'scan_configs.drivers.aliyun', []), [
                            'access_key_id', 'access_key_secret', 'region_id', 'scenes'
                        ])),
                    ]);
                } else {
                    $aliyunV2Driver = Driver::create([
                        'type' => DriverType::Scan,
                        'name' => "{$group->name} 组图片安全 - 阿里云 V2",
                        'options' => array_merge([
                            'provider' => ScanProvider::AliyunV2->value
                        ], Arr::only(data_get($groupConfigs, 'scan_configs.drivers.aliyun', []), [
                            'endpoint', 'access_key_id', 'access_key_secret', 'service', 'region_id', 'scenes'
                        ])),
                    ]);
                }

                $nsfwjsDriver = Driver::create([
                    'type' => DriverType::Scan,
                    'name' => "{$group->name} 组图片安全 - NsfwJS",
                    'options' => [
                        'provider' => ScanProvider::NsfwJS->value,
                        'endpoint' => data_get($groupConfigs, 'scan_configs.drivers.nsfwjs.api_url', ''),
                        'attr_name' => data_get($groupConfigs, 'scan_configs.drivers.nsfwjs.attr_name', ''),
                        'threshold' => data_get($groupConfigs, 'scan_configs.drivers.nsfwjs.threshold', ''),
                        'scenes' => ['porn', 'terrorism', 'ad', 'qrcode', 'live', 'logo', 'drawing', 'hentai', 'neutral', 'sexy']
                    ],
                ]);

                $tencentDriver = Driver::create([
                    'type' => DriverType::Scan,
                    'name' => "{$group->name} 组图片安全 - 腾讯云增强版",
                    'options' => array_merge([
                        'provider' => ScanProvider::Tencent->value,
                        'region_id' => data_get($groupConfigs, 'scan_configs.drivers.tencent.region', ''),
                    ], Arr::only(data_get($groupConfigs, 'scan_configs.drivers.tencent', []), [
                        'endpoint', 'secret_id', 'secret_key', 'biz_type',
                    ]),
                    ),
                ]);

                $moderateContentDriver = Driver::create([
                    'type' => DriverType::Scan,
                    'name' => "{$group->name} 组图片安全 - ModerateContent",
                    'options' => [
                        'provider' => ScanProvider::ModerateContent->value,
                        'api_key' => data_get($groupConfigs, 'scan_configs.drivers.moderatecontent.api_key', ''),
                    ],
                ]);

                // 关联邮件驱动
                $tempGroup->mailDrivers()->attach($mailDriver->id);
            }

            // 迁移策略
            $strategies = DB::connection($connection)->table('strategies')->get()->toArray();
            $storages = [];
            $enums = [
                1 => StorageProvider::Local,
                2 => StorageProvider::S3,
                3 => StorageProvider::Oss,
                4 => StorageProvider::Cos,
                5 => StorageProvider::Qiniu,
                6 => StorageProvider::Upyun,
                7 => StorageProvider::Sftp,
                8 => StorageProvider::Ftp,
                9 => StorageProvider::Webdav,
                10 => StorageProvider::S3,
            ];

            foreach ($strategies as $strategy) {
                $options = json_decode($strategy->configs, true);
                $url = data_get($options, 'url');
                $parsedUrl = parse_url($url);
                $baseUrl = '';

                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
                }

                if (!array_key_exists($strategy->key, $enums)) {
                    // 跳过多吉云等不存在的策略
                    continue;
                }

                /** @var Storage $storage */
                $storage = Storage::create([
                    'name' => $strategy->name,
                    'intro' => $strategy->intro,
                    'provider' => $enums[$strategy->key],
                    'prefix' => ltrim($parsedUrl['path'] ?? '', '/'),
                    'options' => array_filter(array_merge(Arr::except($options, ['url', 'queries']), [
                        'root' => data_get($options, 'root', ''),
                        'public_url' => $baseUrl,
                        'naming_rule' => '{Ymd}/{md5}',
                        'access_key_id' => data_get($options, 'access_key'),
                        'secret_access_key' => data_get($options, 'secret_key'),
                    ])),
                ]);
                $storages[$strategy->id] = $storage->id;
            }

            // 关联组和策略
            foreach (DB::connection($connection)->table('group_strategy')->cursor() as $item) {
                if (!array_key_exists($item->strategy_id, $storages)) {
                    // 跳过不存在的组与策略关联
                    continue;
                }
                GroupStorage::create([
                    'group_id' => $item->group_id,
                    'storage_id' => $storages[$item->strategy_id],
                ]);
            }

            // 默认用户组
            /** @var Group $group */
            $group = Group::where('is_default', true)->first();

            // 迁移用户
            $users = DB::connection($connection)->table('users')->get()->toArray();

            foreach ($users as $item) {
                /** @var User $user */
                $user = User::create(array_merge(Arr::except((array)$item, [
                    'is_adminer', 'capacity', 'configs', 'custom_group_id', 'size',
                    'group_id', 'image_num', 'album_num', 'registered_ip',
                ]), [
                    'is_admin' => $item->is_adminer,
                    'register_ip' => $item->registered_ip,
                    'status' => $item->status == 1 ? UserStatus::Normal : UserStatus::Frozen,
                ]));

                // 查找用户所有订阅
                $subscribes = DB::connection($connection)->table('subscribes')
                    ->where('user_id', $item->id)
                    ->whereNotNull('expired_at')
                    ->whereNull('deleted_at')
                    ->orderBy('expired_at')
                    ->get()
                    ->toArray();

                // 总容量(kb)
                $capacity = round($item->capacity, 2);
                foreach ($subscribes as $subscribe) {
                    $product = json_decode($subscribe->product, true);
                    $capacity = max($capacity - round(data_get($product, 'capacity', 0), 2), 0);

                    // 创建可用组
                    $subscribeGroup = Group::find(data_get($product, 'group_id'));
                    if ($subscribeGroup) {
                        $user->groups()->create([
                            'group_id' => $subscribeGroup->id,
                            'from' => UserGroupFrom::Subscribe,
                            'expired_at' => $subscribe->expired_at,
                            'created_at' => $subscribe->created_at,
                            'updated_at' => $subscribe->updated_at,
                        ]);
                    }

                    // 创建可用容量
                    $user->capacities()->create([
                        'capacity' => round(data_get($product, 'capacity', 0), 2),
                        'from' => UserCapacityFrom::Subscribe,
                        'expired_at' => $subscribe->expired_at,
                        'created_at' => $subscribe->created_at,
                        'updated_at' => $subscribe->updated_at,
                    ]);
                }

                // 创建该用户初始容量
                $user->capacities()->create([
                    'capacity' => $capacity,
                    'from' => UserCapacityFrom::System,
                ]);

                // 创建用户初始组
                $user->groups()->create([
                    'group_id' => $group->id,
                    'from' => UserGroupFrom::System,
                ]);
            }

            // 迁移套餐
            foreach (DB::connection($connection)->table('plans')->cursor() as $item) {
                $array = array_merge(Arr::only((array)$item, ['id', 'name', 'intro', 'badge', 'sort', 'updated_at', 'created_at']), [
                    'features' => json_decode($item->features, true),
                    'is_up' => true,
                ]);

                /** @var Plan $plan */
                $plan = Plan::create($array);

                foreach (json_decode($item->prices, true) as $price) {
                    // 创建价格
                    $plan->prices()->create(array_merge(Arr::only($price, ['id', 'name', 'updated_at', 'created_at']), [
                        // 秒转换成分钟
                        'duration' => min($price['duration'] < 60 ? 1 : $price['duration'] / 60, 999999),
                        'price' => min($price['price'], 999999),
                    ]));
                }
            }

            // 迁移优惠券
            /*foreach (DB::connection($connection)->table('coupons')->cursor() as $item) {
                Coupon::create(array_merge(Arr::only((array)$item, ['id', 'name', 'code', 'value', 'expired_at', 'updated_at', 'created_at']), [
                    'usage_limit' => $item->type == 1 ? 1 : 999,
                    'type' => $item->discount_type == 1 ? CouponType::Direct : CouponType::Percent,
                ]));
            }*/

            // 迁移工单
            foreach (DB::connection($connection)->table('tickets')->cursor() as $item) {
                $ticket = Ticket::create(array_merge(Arr::only((array)$item, ['id', 'user_id', 'issue_no', 'title', 'updated_at', 'created_at']), [
                    'level' => [1 => TicketLevel::Low, 2 => TicketLevel::Medium, 3 => TicketLevel::High][$item->level],
                    'status' => [1 => TicketStatus::InProgress, 2 => TicketStatus::Completed][$item->status],
                ]));

                foreach (DB::connection($connection)->table('ticket_replies')->where('ticket_id', $item->id)->get()->toArray() as $reply) {
                    $ticket->replies()->create(array_merge(Arr::only((array)$reply, ['id', 'ticket_id', 'user_id', 'content', 'updated_at', 'created_at']), [
                        'is_notify' => true,
                        'read_at' => $reply->is_read ? now() : null,
                    ]));
                }
            }

            // 迁移图片
            foreach (DB::connection($connection)->table('images')->whereNull('deleted_at')->cursor() as $item) {
                // 没有指定储存的图片则直接跳过
                if (! $item->strategy_id) continue;

                if (!array_key_exists($item->strategy_id, $storages)) {
                    // 跳过不存在的组与策略关联
                    continue;
                }

                Photo::create(array_merge(Arr::only((array)$item, ['id', 'group_id', 'user_id', 'intro', 'mimetype', 'size', 'md5', 'sha1', 'extension', 'width', 'height', 'expired_at', 'updated_at', 'created_at']), [
                    'storage_id' => $storages[$item->strategy_id],
                    'name' => $item->alias_name ?: $item->origin_name,
                    'filename' => $item->origin_name,
                    'pathname' => Str::rtrim($item->path, '/') . '/' . Str::ltrim($item->name, '/'),
                    'is_public' => $item->permission == 1,
                    'ip_address' => $item->uploaded_ip,
                ]));
            }

            // 迁移相册
            foreach (DB::connection($connection)->table('albums')->cursor() as $item) {
                /** @var Album $album */
                $album = Album::create(array_merge(Arr::only((array)$item, ['id', 'user_id', 'name', 'intro', 'updated_at', 'created_at']), [
                    'is_public' => $item->permission == 1,
                ]));

                // 关联相册
                $ids = Photo::whereIn('id', DB::connection($connection)->table('images')->whereNull('deleted_at')->where('album_id', $album->id)->pluck('id')->toArray())->pluck('id')->toArray();
                $album->photos()->attach($ids);
            }

            // 迁移分享
            foreach (DB::connection($connection)->table('shares')->cursor() as $item) {
                /** @var Share $share */
                $share = Share::create(array_merge(Arr::only((array)$item, ['id', 'user_id', 'content', 'password', 'expired_at', 'updated_at', 'created_at']), [
                    'type' => $item->type == 1 ? ShareType::Photo : ShareType::Album,
                    'slug' => $item->key,
                    'view_count' => $item->view_num,
                ]));

                if ($share->type === ShareType::Photo) {
                    $share->photos()->attach(Photo::whereIn('id', DB::connection($connection)->table('share_images')->pluck('image_id')->toArray())->pluck('id')->toArray());
                } else {
                    // 相册单次只能分享一个
                    $albumId = Album::whereIn('id', DB::connection($connection)->table('share_albums')->pluck('album_id')->toArray())->value('id');
                    if ($albumId) {
                        $share->albums()->attach($albumId);
                    }
                }
            }

            // 迁移订单
            foreach (DB::connection($connection)->table('orders')->cursor() as $item) {
                if (!is_null($item->canceled_at)) {
                    $status = OrderStatus::Cancelled;
                } elseif (!is_null($item->paid_at)) {
                    $status = OrderStatus::Paid;
                } else {
                    $status = OrderStatus::Unpaid;
                }

                Order::create(array_merge(Arr::only((array)$item, ['id', 'user_id', 'trade_no', 'status', 'pay_method', 'paid_at', 'canceled_at', 'updated_at', 'created_at']), [
                    'out_trade_no' => $item->trade_no,
                    'type' => OrderType::Plan,
                    'amount' => $item->amount,
                    'snapshot' => json_decode($item->snapshot, true),
                    'product' => json_decode($item->product, true),
                    'status' => $status,
                ]));
            }

            // 迁移公告
            foreach (DB::connection($connection)->table('notices')->cursor() as $item) {
                Notice::create(array_merge(Arr::only((array)$item, ['id', 'title', 'content', 'updated_at', 'created_at']), [
                    'view_count' => $item->view_num,
                ]));
            }

            // 迁移举报
            foreach (DB::connection($connection)->table('reports')->cursor() as $item) {
                /** @var Photo $photo */
                $photo = Photo::find($item->image_id);
                if (!is_null($photo)) {
                    $photo->reports()->create(array_merge(Arr::only((array)$item, ['id', 'ip_address', 'updated_at', 'created_at']), [
                        'report_user_id' => $photo->user_id,
                        'content' => $item->reason,
                        'status' => ReportStatus::Handled,
                        'handled_at' => $item->updated_at,
                    ]));
                }
            }

            // 迁移违规记录
            foreach (DB::connection($connection)->table('violations')->cursor() as $item) {
                Violation::create(array_merge(Arr::only((array)$item, ['id', 'updated_at', 'created_at']), [
                    'user_id' => User::where('id', $item->user_id)->exists() ? $item->user_id : null,
                    'photo_id' => Photo::where('id', $item->image_id)->exists() ? $item->image_id : null,
                    'reason' => $item->desc,
                    'status' => ViolationStatus::Handled,
                    'handled_at' => $item->updated_at,
                ]));
            }

            // 迁移页面
            foreach (DB::connection($connection)->table('pages')->cursor() as $item) {
                Page::create(array_merge(Arr::only((array)$item, ['id', 'icon', 'name', 'slug', 'title', 'keywords', 'description', 'url', 'sort', 'is_show', 'updated_at', 'created_at']), [
                    'type' => $item->type == 1 ? PageType::Internal : PageType::External,
                    'view_count' => $item->click_num,
                    'content' => '请补充...'
                ]));
            }

            // 迁移意见与反馈
            foreach (DB::connection($connection)->table('feedbacks')->cursor() as $item) {
                Feedback::create(array_merge(Arr::only((array)$item, ['id', 'name', 'email', 'content', 'ip_address', 'updated_at', 'created_at']), [
                    'type' => $item->type == 1 ? FeedbackType::General : FeedbackType::Dmca,
                    'title' => Str::limit($item->content, 60),
                ]));
            }

            // 迁移第三方授权
            foreach (DB::connection($connection)->table('auths')->cursor() as $item) {
                OAuth::create([
                    'openid' => $item->client_id,
                    'driver_id' => $socialiteDrivers[$item->client_type],
                    'user_id' => $item->user_id,
                ]);
            }

            DB::commit();

            info("数据迁移完成，由于数据结构变动较大，迁移后仍需要手动调整设置，例如角色组与储存的关联、套餐中配置的角色组和容量、补充页面内容等等...\n更多请访问 https://docs.lsky.pro，按照迁移文档进行操作，迁移过程中遇到困难可添加官方QQ群寻求帮助。");
        } catch (Throwable $e) {
            error("迁移失败，错误行：{$e->getLine()}，错误信息：{$e->getMessage()}");
            error($e->getTraceAsString());
            try {
                DB::rollBack();
            } catch (Throwable $e) {

            }
            return CommandAlias::FAILURE;
        }

        return CommandAlias::SUCCESS;
    }

    protected function getDbConnection(): string
    {
        return select(
            label: '旧版本数据库类型：',
            options: [
                'sqlite' => 'SQLite 3.35.0+',
                'mysql' => 'MySQL 5.7+',
                'mariadb' => 'MariaDB 10.3+',
                'pgsql' => 'PostgreSQL 10.0+',
                'sqlsrv' => 'SQL Server 2017+',
            ],
            required: true,
        );
    }

    protected function getSqliteFilepath(): string
    {
        return text(
            label: '旧版本 Sqlite 文件绝对路径：',
            placeholder: 'e.g：' . Config::get('database.connections.sqlite.database'),
            required: true,
        );
    }

    protected function getDbHost(): string
    {
        return text(
            label: '旧版本数据库连接地址（默认 127.0.0.1）：',
            placeholder: '请输入数据库连接地址',
            default: '127.0.0.1',
            required: true,
        );
    }

    protected function getDbPort(): string
    {
        return text(
            label: '旧版本数据库连接端口（默认 3306）：',
            placeholder: '请输入数据库连接端口',
            default: '3306',
            required: true,
        );
    }

    protected function getDbDatabase(): string
    {
        return text(
            label: '旧版本数据库名称：',
            placeholder: '请输入数据库名称',
            required: true,
        );
    }

    protected function getDbUsername(): string
    {
        return text(
            label: '旧版本数据库连接用户名：',
            placeholder: '请输入数据库连接用户名',
            default: 'root',
            required: true,
        );
    }

    protected function getDbPassword(): string
    {
        return password(
            label: '旧版本数据库连接密码：',
            placeholder: '请输入数据库连接密码',
            required: true,
        );
    }
}
