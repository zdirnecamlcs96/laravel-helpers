<?php

namespace Zdirnecamlcs96\Helpers\Traits;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

trait Datatable
{
    use Requests;

    private function __generateQuery(Builder $query, string $class, array $columns, string $search)
    {

        foreach ($columns as $key => $column) {
            if ($key === array_key_first($columns)) {
                $query->where($column, 'LIKE', "%{$search}%");
            } else {
                $query->orWhere($column, 'LIKE', "%{$search}%");
            }
        }

        return $query;
    }

    public function __datatableResponse(Request $request, string $class, array $columns, string $resource)
    {
        try {

            $draw = $this->__requestFilled('draw', 1);
            $start = $this->__requestFilled('start', 0);
            $limit = $this->__requestFilled('length', 10);

            $totalRecords = $class::count();

            $search = $request->get('search')['value'] ?? null;

            $builder = $class::when($search, fn($query) =>
                $query
                    ->where(fn($query) =>
                            $this->__generateQuery($query, $class, $columns, $search) ))
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
