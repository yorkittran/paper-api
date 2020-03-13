<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Arr;
use Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Builder::macro('whereLike', function ($attributes, $search = null) {
            $this->where(function (Builder $query) use ($attributes, $search) {
                foreach (Arr::wrap($attributes) as $attribute) {
                    if ($search) {
                        $terms = preg_split('/\s+/', $search);
                        foreach (Arr::wrap($terms) as $term) {
                            $query->when(
                                Str::contains($attribute, '.'),
                                function (Builder $query) use ($attribute, $term) {
                                    [$relationName, $relationAttribute] = explode('.', $attribute);

                                    $query->orWhereHas($relationName, function (Builder $query) use ($relationAttribute, $term) {
                                        $query->where($relationAttribute, 'LIKE', "%{$term}%");
                                    });
                                },
                                function (Builder $query) use ($attribute, $term) {
                                    $query->orWhere($attribute, 'LIKE', "%{$term}%");
                                }
                            );
                        }
                    }
                }
            });

            return $this;
        });
    }
}
