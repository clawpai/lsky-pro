<?php

declare(strict_types=1);

namespace App;

/**
 * API权限枚举
 */
enum ApiPermission: string
{
    // 用户资料相关
    case USER_PROFILE_READ = 'user:profile:read';
    case USER_PROFILE_WRITE = 'user:profile:write';
    
    // 用户令牌相关
    case USER_TOKEN_READ = 'user:token:read';
    case USER_TOKEN_WRITE = 'user:token:write';
    
    // 用户角色组相关
    case USER_GROUP_READ = 'user:group:read';
    case USER_GROUP_WRITE = 'user:group:write';
    
    // 用户容量相关
    case USER_CAPACITY_READ = 'user:capacity:read';
    case USER_CAPACITY_WRITE = 'user:capacity:write';
    
    // 相册相关
    case USER_ALBUM_READ = 'user:album:read';
    case USER_ALBUM_WRITE = 'user:album:write';
    
    // 照片相关
    case USER_PHOTO_READ = 'user:photo:read';
    case USER_PHOTO_WRITE = 'user:photo:write';
    
    // 分享相关
    case USER_SHARE_READ = 'user:share:read';
    case USER_SHARE_WRITE = 'user:share:write';
    
    // 工单相关
    case USER_TICKET_READ = 'user:ticket:read';
    case USER_TICKET_WRITE = 'user:ticket:write';
    
    // 订单相关
    case USER_ORDER_READ = 'user:order:read';
    case USER_ORDER_WRITE = 'user:order:write';
    
    // OAuth相关
    case OAUTH_READ = 'oauth:read';
    case OAUTH_WRITE = 'oauth:write';
    
    // 广场相关
    case EXPLORE_READ = 'explore:read';
    case EXPLORE_WRITE = 'explore:write';
    
    // 上传相关
    case UPLOAD_WRITE = 'upload:write';
    
    // 基础设置（能够被所有令牌访问的权限）
    case BASIC = 'basic';
    
    /**
     * 获取API权限的中文描述
     */
    public function getDescription(): string
    {
        return match($this) {
            self::USER_PROFILE_READ => '读取用户资料',
            self::USER_PROFILE_WRITE => '更新用户资料',
            self::USER_TOKEN_READ => '查看个人令牌',
            self::USER_TOKEN_WRITE => '管理个人令牌',
            self::USER_GROUP_READ => '查看用户角色组',
            self::USER_GROUP_WRITE => '管理用户角色组',
            self::USER_CAPACITY_READ => '查看用户容量',
            self::USER_CAPACITY_WRITE => '管理用户容量',
            self::USER_ALBUM_READ => '查看相册',
            self::USER_ALBUM_WRITE => '管理相册',
            self::USER_PHOTO_READ => '查看照片',
            self::USER_PHOTO_WRITE => '管理照片',
            self::USER_SHARE_READ => '查看分享',
            self::USER_SHARE_WRITE => '管理分享',
            self::USER_TICKET_READ => '查看工单',
            self::USER_TICKET_WRITE => '管理工单',
            self::USER_ORDER_READ => '查看订单',
            self::USER_ORDER_WRITE => '管理订单',
            self::OAUTH_READ => '查看OAuth信息',
            self::OAUTH_WRITE => '管理OAuth绑定',
            self::EXPLORE_READ => '浏览广场内容',
            self::EXPLORE_WRITE => '管理广场互动',
            self::UPLOAD_WRITE => '上传图片',
            self::BASIC => '基础权限',
        };
    }
    
    /**
     * 获取API权限的详细描述
     */
    public function getDetailedDescription(): string
    {
        return match($this) {
            self::USER_PROFILE_READ => '允许读取用户个人资料，包括昵称、头像、邮箱、手机号等基本信息。',
            self::USER_PROFILE_WRITE => '允许修改用户个人资料，包括更新昵称、头像、绑定/解绑邮箱和手机号。',
            self::USER_TOKEN_READ => '允许查看用户创建的所有访问令牌列表，包括令牌名称、创建时间和过期时间。',
            self::USER_TOKEN_WRITE => '允许创建新的访问令牌或删除现有令牌，管理令牌的权限范围和有效期。',
            self::USER_GROUP_READ => '允许查看用户所属的角色组信息，包括组名称、权限等级和加入时间。',
            self::USER_GROUP_WRITE => '允许管理用户角色组，包括创建、修改或删除角色组，以及调整组内权限。',
            self::USER_CAPACITY_READ => '允许查看用户存储容量信息，包括总容量、已使用容量和各类资源占用情况。',
            self::USER_CAPACITY_WRITE => '允许管理用户存储容量，包括扩容、调整限制和重置配额。',
            self::USER_ALBUM_READ => '允许查看用户创建的相册列表和相册内容，包括名称、创建时间和包含照片。',
            self::USER_ALBUM_WRITE => '允许创建、编辑和删除相册，调整相册属性如名称、封面和访问权限。',
            self::USER_PHOTO_READ => '允许查看用户上传的照片，包括照片信息、元数据和预览图。',
            self::USER_PHOTO_WRITE => '允许上传、编辑和删除照片，修改照片属性如标题、描述和标签。',
            self::USER_SHARE_READ => '允许查看用户创建的分享内容列表，包括分享链接、访问权限和过期设置。',
            self::USER_SHARE_WRITE => '允许创建新的分享或管理现有分享，包括修改分享设置和删除分享。',
            self::USER_TICKET_READ => '允许查看用户提交的工单列表和工单详情，包括状态和回复内容。',
            self::USER_TICKET_WRITE => '允许创建新工单、回复工单或关闭工单，管理工单优先级和标签。',
            self::USER_ORDER_READ => '允许查看用户的订单历史和订单详情，包括订单状态、支付信息和商品内容。',
            self::USER_ORDER_WRITE => '允许创建新订单、取消订单或申请退款，管理订单相关操作。',
            self::OAUTH_READ => '允许查看用户的OAuth绑定信息，包括已绑定的第三方账号列表和授权状态。',
            self::OAUTH_WRITE => '允许管理OAuth绑定，包括绑定新的第三方账号或解除现有绑定。',
            self::EXPLORE_READ => '允许浏览和查看广场页面的公开内容，包括推荐内容和热门分享。',
            self::EXPLORE_WRITE => '允许在广场内容上进行互动，如点赞公开分享的内容。',
            self::UPLOAD_WRITE => '允许上传新图片到系统，包括单张上传或批量上传，自动处理图片格式。',
            self::BASIC => '基础访问权限，包括查看系统配置、公告、页面和套餐信息等公共API的访问权限。此权限是所有令牌的默认权限。',
        };
    }
    
    /**
     * 获取权限组列表及其包含的权限
     */
    public static function getPermissionGroups(): array
    {
        return [
            'user' => [
                '用户资料' => [self::USER_PROFILE_READ, self::USER_PROFILE_WRITE],
                '访问令牌' => [self::USER_TOKEN_READ, self::USER_TOKEN_WRITE],
                '角色组' => [self::USER_GROUP_READ, self::USER_GROUP_WRITE],
                '存储容量' => [self::USER_CAPACITY_READ, self::USER_CAPACITY_WRITE],
            ],
            'content' => [
                '相册管理' => [self::USER_ALBUM_READ, self::USER_ALBUM_WRITE],
                '照片管理' => [self::USER_PHOTO_READ, self::USER_PHOTO_WRITE],
                '内容分享' => [self::USER_SHARE_READ, self::USER_SHARE_WRITE],
                '内容上传' => [self::UPLOAD_WRITE],
            ],
            'service' => [
                '工单服务' => [self::USER_TICKET_READ, self::USER_TICKET_WRITE],
                '订单管理' => [self::USER_ORDER_READ, self::USER_ORDER_WRITE],
            ],
            'integration' => [
                'OAuth集成' => [self::OAUTH_READ, self::OAUTH_WRITE],
                '内容广场' => [self::EXPLORE_READ, self::EXPLORE_WRITE],
            ],
            'basic' => [
                '基础功能' => [self::BASIC],
            ],
        ];
    }
    
    /**
     * 获取所有权限列表，用于前端展示
     */
    public static function getPermissionList(): array
    {
        $permissions = [];
        foreach (self::cases() as $permission) {
            $permissions[] = [
                'value' => $permission->value,
                'label' => $permission->getDescription(),
                'detail' => $permission->getDetailedDescription(),
                'category' => explode(':', $permission->value)[0],
            ];
        }
        return $permissions;
    }
    
    /**
     * 获取所有权限，包括描述信息
     */
    public static function getAllPermissions(): array
    {
        $permissions = [];
        foreach (self::cases() as $permission) {
            $permissions[$permission->value] = $permission->getDescription();
        }
        return $permissions;
    }
    
    /**
     * 获取路由权限映射关系
     */
    public static function getRoutePermissionMap(): array
    {
        return [
            // 用户信息相关
            'api/v2/user/profile' => [
                'GET' => self::USER_PROFILE_READ,
                'POST' => self::USER_PROFILE_WRITE,
            ],
            'api/v2/user/setting' => [
                'POST' => self::USER_PROFILE_WRITE,
            ],
            'api/v2/user/bind_phone' => [
                'POST' => self::USER_PROFILE_WRITE,
            ],
            'api/v2/user/bind_email' => [
                'POST' => self::USER_PROFILE_WRITE,
            ],
            
            // 令牌相关
            'api/v2/user/tokens' => [
                'GET' => self::USER_TOKEN_READ,
                'POST' => self::USER_TOKEN_WRITE,
                'DELETE' => self::USER_TOKEN_WRITE,
            ],
            'api/v2/user/tokens/user-permissions' => [
                'GET' => self::USER_TOKEN_READ,
            ],
            
            // 角色组相关
            'api/v2/user/groups' => [
                'GET' => self::USER_GROUP_READ,
                'DELETE' => self::USER_GROUP_WRITE,
            ],
            
            // 容量相关
            'api/v2/user/capacities' => [
                'GET' => self::USER_CAPACITY_READ,
                'DELETE' => self::USER_CAPACITY_WRITE,
            ],
            
            // 相册相关
            'api/v2/user/albums' => [
                'GET' => self::USER_ALBUM_READ,
                'POST' => self::USER_ALBUM_WRITE,
                'PUT' => self::USER_ALBUM_WRITE,
                'DELETE' => self::USER_ALBUM_WRITE,
            ],
            'api/v2/user/albums/*' => [
                'GET' => self::USER_ALBUM_READ,
                'POST' => self::USER_ALBUM_WRITE,
                'PUT' => self::USER_ALBUM_WRITE,
                'DELETE' => self::USER_ALBUM_WRITE,
            ],
            
            // 照片相关
            'api/v2/user/photos' => [
                'GET' => self::USER_PHOTO_READ,
                'DELETE' => self::USER_PHOTO_WRITE,
            ],
            'api/v2/user/photos/*' => [
                'GET' => self::USER_PHOTO_READ,
                'POST' => self::USER_PHOTO_WRITE,
                'PUT' => self::USER_PHOTO_WRITE,
                'DELETE' => self::USER_PHOTO_WRITE,
            ],
            'api/v2/user/photos/timeline' => [
                'GET' => self::USER_PHOTO_READ,
            ],
            
            // 分享相关
            'api/v2/user/shares' => [
                'GET' => self::USER_SHARE_READ,
                'POST' => self::USER_SHARE_WRITE,
                'PUT' => self::USER_SHARE_WRITE,
                'DELETE' => self::USER_SHARE_WRITE,
            ],
            
            // 工单相关
            'api/v2/user/tickets' => [
                'GET' => self::USER_TICKET_READ,
                'POST' => self::USER_TICKET_WRITE,
                'DELETE' => self::USER_TICKET_WRITE,
            ],
            'api/v2/user/tickets/*' => [
                'GET' => self::USER_TICKET_READ,
                'POST' => self::USER_TICKET_WRITE,
                'PUT' => self::USER_TICKET_WRITE,
            ],
            
            // 订单相关
            'api/v2/user/orders' => [
                'GET' => self::USER_ORDER_READ,
                'POST' => self::USER_ORDER_WRITE,
            ],
            'api/v2/user/orders/*' => [
                'GET' => self::USER_ORDER_READ,
                'POST' => self::USER_ORDER_WRITE,
                'PUT' => self::USER_ORDER_WRITE,
            ],
            'api/v2/user/orders/preview' => [
                'POST' => self::USER_ORDER_READ,
            ],
            
            // OAuth相关
            'api/v2/oauth/binds' => [
                'GET' => self::OAUTH_READ,
            ],
            'api/v2/oauth/*' => [
                'POST' => self::OAUTH_WRITE,
                'DELETE' => self::OAUTH_WRITE,
            ],
            
            // 广场相关
            'api/v2/explore/*' => [
                'GET' => self::EXPLORE_READ,
                'POST' => self::EXPLORE_WRITE,
                'DELETE' => self::EXPLORE_WRITE,
            ],
            'api/v2/shares/*' => [
                'GET' => self::EXPLORE_READ,
                'POST' => self::EXPLORE_WRITE,
                'DELETE' => self::EXPLORE_WRITE,
            ],
            
            // 上传相关
            'api/v2/upload' => [
                'POST' => self::UPLOAD_WRITE,
            ],
            'api/v1/upload' => [
                'POST' => self::UPLOAD_WRITE,
            ],
            'api/v1/images/tokens' => [
                'POST' => self::UPLOAD_WRITE,
            ],
            
            // 基础公共API（不需要特殊权限）
            'api/v2/configs' => [
                'GET' => self::BASIC,
            ],
            'api/v2/group' => [
                'GET' => self::BASIC,
            ],
            'api/v2/notices/*' => [
                'GET' => self::BASIC,
            ],
            'api/v2/pages/*' => [
                'GET' => self::BASIC,
            ],
            'api/v2/plans/*' => [
                'GET' => self::BASIC,
            ],
        ];
    }
} 