<?php
namespace tests\Relation;

use WScore\ScoreDB\DB;

trait testToolsTrait
{
    static function loadClasses()
    {
        class_exists( 'WScore\ScoreDB\DB' );
        class_exists( 'WScore\ScoreDB\DbAccess' );
        class_exists( 'WScore\ScoreDB\Hooks\Hooks' );
        class_exists( 'tests\ScoreDB\Dao\User' );
    }

    function prepareTest( $dbType )
    {
        DB::restart();
        /** @noinspection PhpIncludeInspection */
        DB::config( include( dirname(__DIR__) . "/configs/{$dbType}-config.php" ) );
        $pdo = DB::connect();

        /** @noinspection PhpIncludeInspection */
        $pdo->query( include( dirname(__DIR__) . "/configs/{$dbType}-blogs.php" ) );
    }

    /**
     * @param int $idx
     * @return array
     */
    function makeUserDataAsArray( $idx=1 )
    {
        $data = [
            'name' => 'test-' . $idx ,
            'age'  => 30 + $idx,
            'gender' => 1 + $idx%2,
            'status' => 1 + $idx%3,
            'bday' => (new \DateTime('1989-01-01'))->add(new \DateInterval('P1D'))->format('Y-m-d'),
            'no_null' => 'not null test: ' . mt_rand(1000,9999),
        ];
        return $data;
    }

    /**
     * @param int $idx
     * @return array
     */
    function makeBlogDataAsArray( $idx=1 )
    {
        $data = [
            'status' => 1,
            'title' => 'title-' . $idx ,
            'content' => 'blog content: ' . mt_rand(1000,9999),
        ];
        return $data;
    }
}