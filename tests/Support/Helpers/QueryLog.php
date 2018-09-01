<?php

namespace Tests\Support\Helpers;

use Illuminate\Support\Facades\DB;

trait QueryLog
{

    /**
     * Start logging queries run through Laravel
     */
    public function startLogging()
    {
        DB::enableQueryLog();
    }

    /**
     * Stop logging queries run through Laravel
     */
    public function stopLogging()
    {
        DB::disableQueryLog();
    }

    /**
     * Get a parsed log of quries run, values used will replace bindings in string
     * @return array
     */
    public function getQueries()
    {
        $queries = DB::getQueryLog();
        $formattedQueries = [];
        foreach ($queries as $query) :
            $prep = $query['query'];
            foreach ($query['bindings'] as $binding) :
                $prep = preg_replace("#\?#", $binding, $prep, 1);
            endforeach;
            $formattedQueries[] = $prep;
        endforeach;
        return $formattedQueries;
    }
}
