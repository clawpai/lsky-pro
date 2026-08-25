<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class InstallRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'app_name' => ['required'],
            'app_url' => ['required', 'url'],
            'app_license_key' => ['required'],
            'db_connection' => ['required', 'in:sqlite,mysql,mariadb,pgsql,sqlsrv'],
            'db_host' => ['required_if:db_connection,mysql,mariadb,pgsql,sqlsrv'],
            'db_port' => ['required_if:db_connection,mysql,mariadb,pgsql,sqlsrv'],
            'db_database' => ['required_if:db_connection,mysql,mariadb,pgsql,sqlsrv'],
            'db_username' => ['required_if:db_connection,mysql,mariadb,pgsql,sqlsrv'],
            'db_password' => ['required_if:db_connection,mysql,mariadb,pgsql,sqlsrv'],
            'admin_username' => ['required', 'alpha_dash'],
            'admin_email' => ['required', 'email'],
            'admin_password' => ['required', 'confirmed'],
        ];
    }

    public function attributes(): array
    {
        return [
            'app_name' => '应用名称',
            'app_url' => '应用 URL',
            'app_license_key' => '授权密钥',
            'db_connection' => '数据库驱动',
            'db_host' => '数据库连接地址',
            'db_port' => '数据库连接端口',
            'db_database' => '数据库名称',
            'db_username' => '数据库用户名',
            'db_password' => '数据库密码',
            'admin_username' => '管理员用户名',
            'admin_email' => '管理员邮箱',
            'admin_password' => '管理员密码',
        ];
    }
}
