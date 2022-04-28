<?php

namespace Zdirnecamlcs96\Helpers\Traits;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

trait Datatable
{
    use Requests;

    /**
     * Search Query Builder
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query     Eloquent query
     * @param  string $class                                    Class name with namesapce
     * @param  array $columns                                   List of the table columns
     * @param  string $search                                   Keyword for searching/filtering purpose
     * @param  array $relationships                             List of relationships with thier columns
     *                                                          Eg. [ 'relationshipA' => 'columnA', 'relationshipB' => 'columnB' ]
     * @return void
     */
    private function __generateQuery(Builder $query, string $class, array $columns, string $search, array $relationships)
    {

        foreach ($columns as $key => $column) {

            $nested = (array) explode('.', $column);

            if (sizeof($nested) > 1)
            {
                $values = array_values($nested);
                $column = end($values);
                array_pop($nested);
                $relation = implode('.', $nested); // Get relation

                $query->orWhereHas($relation, fn($query) => $query->where($column, 'LIKE', "%{$search}%"));
            }
            else if (in_array($column, array_keys($relationships)))
            {
                $relationColumn = $relationships[$column];
                $query->orWhereHas($column, fn($query) => $query->where($relationColumn, 'LIKE', "%{$search}%"));
            }
            else
            {
                if ($key === array_key_first($columns)) {
                    $query->where($column, 'LIKE', "%{$search}%");
                } else {
                    $query->orWhere($column, 'LIKE', "%{$search}%");
                }
            }
        }

        return $query;
    }

    /**
     * Return a response compatible with Jquery Datatable [https://datatables.net/]
     *
     * @param  \Illuminate\Http\Request $request    HTTP request
     * @param  string $class                        Class name with namesapce
     * @param  array $columns                       List of the table columns
     * @param  string $resource                      Class name with namespace that extended \Illuminate\Http\Resources\Json\JsonResource
     * @param  array $relationships                 Optional. List of relationships with thier columns
     *                                              Eg. [ 'relationshipA' => 'columnA', 'relationshipB' => 'columnB' ]
     * @param  mixed|null $queries                  Optional. Extra queries function
     * @return void
     */
    public function __datatableResponse(Request $request, string $class, array $columns, string $resource, array $relationships = [], $queries = null)
    {
        try {

            $draw = $this->__requestFilled('draw', 1);
            $start = $this->__requestFilled('start', 0);
            $limit = $this->__requestFilled('length', 10);

            $totalRecords = $class::count();

            $search = $request->get('search')['value'] ?? null;

            $builder = $class::with(array_keys($relationships))
            ->when($queries, $queries)
            ->when($search, fn($query) =>
                $query
                    ->where(fn($query) =>
                            $this->__generateQuery($query, $class, $columns, $search, $relationships) ))
                                ->latest();

            $records = (clone $builder)
                            ->offset($start)
                            ->limit($limit)
                            ->get();

            $totalFilteredRecords = (clone $builder)->count();

            return $this->__apiDataTable(($resource::collection($records)), $totalRecords, $draw, $totalFilteredRecords);

        } catch (\Throwable $th) {

            throw new Exception($th->getMessage());

        }

    }
}
