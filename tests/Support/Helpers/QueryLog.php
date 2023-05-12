<?php

namespace Tests\Support\Helpers;

use Illuminate\Support\Facades\DB;

trait QueryLog
{

    /**
     * Start logging queries run through Laravel
     */
    public function startLogging(): void
    {
        DB::enableQueryLog();
    }

    /**
     * Stop logging queries run through Laravel
     */
    public function stopLogging(): void
    {
        DB::disableQueryLog();
    }

    /**
     * Get a parsed log of quries run, values used will replace bindings in string
     */
    public function getQueries(): array
    {
        $queries = DB::getQueryLog();
        $formattedQueries = [];
        foreach ($queries as $query) :
            $prep = $query['query'];
            foreach ($query['bindings'] as $binding) :
                $prep = preg_replace("#\?#", (string) $binding, (string) $prep, 1);
            endforeach;

            $formattedQueries[] = $prep;
        endforeach;

        return $formattedQueries;
    }
}
