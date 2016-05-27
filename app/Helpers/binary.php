<?php

if (!function_exists('is_binary')) {
    function is_binary($input)
    {
        if (is_null($input)) {
            return false;
        }

        if (is_int($input)) {
            return false;
        }

        return !ctype_print($input);
    }
}

if (!function_exists('binary_sql')) {
    function binary_sql($bin)
    {
        if (DB::connection() instanceof \Illuminate\Database\PostgresConnection) {
            $bin = pg_escape_bytea($bin);
            $bin = str_replace("''", "'", $bin);
        }

        return $bin;
    }
}

if (!function_exists('binary_unsql')) {
    function binary_unsql($bin)
    {
        if (is_resource($bin)) {
            $bin = stream_get_contents($bin);
        }

        if (DB::connection() instanceof \Illuminate\Database\PostgresConnection) {
            return pg_unescape_bytea($bin);
        }

        return $bin;
    }
}
