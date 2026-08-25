<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * 数据筛选作用域
 * 语法格式：{columns}:{operator}:{contents}，示例：name:like:%john%
 * columns 支持多个字段，用逗号分隔，示例：name,email 多个以并且的关系查询
 * operator 支持 laravel 的所有 operator，@see Builder::$operators
 * contents 支持多个值，用逗号分隔，示例：%john%,%doe%，多个以或的关系查询
 */
class FilterScope implements Scope
{
    /**
     * 筛选关键字
     *
     * @var string|null
     */
    public ?string $q;

    /**
     * 支持模糊搜索的字段
     *
     * @var array<string>
     */
    public array $likes = [];

    /**
     * 筛选条件
     *
     * @var array
     */
    public array $conditions = [];

    public function __construct(?string $q, array $likes = [], array $conditions = [])
    {
        $this->q = $q ? urldecode($q) : '';
        $this->likes = $likes;
        $this->conditions = [...[
            'sort:oldest' => fn(Builder $builder) => $builder->oldest(),
            'sort:latest' => fn(Builder $builder) => $builder->latest(),
        ], ...$conditions];
    }

    public function apply(Builder $builder, Model $model): void
    {
        if (!$this->q) {
            return;
        }

        $queries = array_filter(explode(' ', $this->q));
        $keywords = [];

        foreach ($queries as $query) {
            // 完整匹配
            if (array_key_exists($query, $this->conditions)) {
                call_user_func_array($this->conditions[$query], [$builder, $model]);
                continue;
            }

            // 表达式匹配
            [$columns, $operator, $contents] = $this->prepareValueAndOperator($query);
            if (count($columns) > 0 && in_array($operator, $builder->getQuery()->operators)) {
                foreach ($columns as $column) {
                    // 跳过不存在的字段，防止sql注入
                    if (!in_array($column, $model->getFillable())) {
                        continue;
                    }

                    $builder->where(function (Builder $builder) use ($column, $operator, $contents) {
                        foreach ($contents as $content) {
                            $builder->orWhere($column, $operator, $content);
                        }
                    });
                }

                continue;
            }

            $keywords[] = $query;
        }

        foreach ($keywords as $keyword) {
            $builder->where(function ($builder) use ($keyword) {
                foreach ($this->likes as $like) {
                    $builder->orWhere($like, 'like', "%{$keyword}%");
                }
            });
        }
    }

    /**
     * 解析筛选表达式
     *
     * @param string $query
     * @return array
     */
    private function prepareValueAndOperator(string $query): array
    {
        $default = [[], null, []];

        // 匹配 {columns}:{operator}:{contents}
        if (preg_match('/^[^:]+:[^:]+:[^:]+$/', $query)) {

            [$columns, $operator, $contents] = explode(':', $query, 3);

            $columns = explode(',', $columns);
            $contents = explode(',', $contents);

            return [$columns, $operator, $contents];
        }

        return $default;
    }
}
