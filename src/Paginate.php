<?php
namespace WScore\DbAccess;

class Paginate
{
    /**
     * @var Query
     */
    protected $query;

    protected $pager = '_page';
    
    protected $limiter = '_limit';
    
    protected $perPage = 20;
    
    protected $currUri = null;
    
    protected $currPage = 1;
    
    protected $saveID = 'Paginated-Query';
    
    protected $total = null;
    
    /**
     * @var array|null
     */
    protected $session = array();

    // +----------------------------------------------------------------------+
    //  managing query
    // +----------------------------------------------------------------------+
    /**
     * @param array|null $session
     */
    public function __construct( $session=null )
    {
        if( is_null( $session ) ) {
            $this->session = $_SESSION;
        } else {
            $this->session = $session;
        }
        $this->currUri = $_SERVER['REQUEST_URI'];
        $this->setSaveId();
        if( $limit = filter_input( INPUT_GET, $this->limiter ) ) {
            $this->perPage = $limit;
        }

    }
    
    protected function setSaveId() {
        $this->saveID = 'Paginated-'.md5( $this->currUri );
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function set( $key, $value )
    {
        if( isset( $this->$key ) ) {
            $this->key = $value;
        }
        return $this;
    }

    /**
     * @param Query $query
     * @param int   $perPage
     * @return $this
     */
    public function setQuery( $query, $perPage=20 )
    {
        $query->limit( $perPage );
        $this->query = $query;
        return $this;
    }

    /**
     * @param int $page
     * @return Query
     */
    public function loadQuery( $page=null )
    {
        if( !$page ) $page = filter_input( INPUT_GET, $this->pager );
        if( !$page ) return null;
        if( !isset($this->session[$this->saveID]) ) return null;
        
        $this->currPage = $page;
        /** @var Query $query */
        $query = $this->session[$this->saveID];
        $query->page( $page );
        $this->perPage = $query->getLimit();
        return $query;
    }

    /**
     * @param Query $query
     */
    public function saveQuery( $query )
    {
        $query->limit( $this->perPage );
        $this->session[$this->saveID] = $query;
        $this->total = $query->count();
    }

    // +----------------------------------------------------------------------+
    //  preparing for pagination list
    // +----------------------------------------------------------------------+
    /**
     * @param int $numLinks
     * @return array
     */
    function getPages( $numLinks = 5 )
    {
        $pages = [
            'found' => $this->total,
            'curr_page' => $this->currPage,
        ];
        $pages[ 'top_page'  ] = 1;
        $pages[ 'last_page' ] = $lastPage = $this->total ? 
            (integer) ( ceil( $this->total / $this->perPage ) ) : 
            $this->currPage + $numLinks
        ;
        // prepare pages
        $pages['page'] = [];
        $start = 
            $this->currPage - $numLinks >= 1 ?
            $this->currPage - $numLinks : 1;
        $last = 
            $this->currPage + $numLinks <= $lastPage ?
            $this->currPage + $numLinks : $lastPage;
        for( $page = $start; $page <= $last; $page++ ) {
            $pages['page'][$page] = $page == $this->currPage ? '' : $page;
        }
        // previous and next pages.
        $pages['prev_page'] = $this->currPage>1 ? $this->currPage-1: 1;
        $pages['next_page'] = $this->currPage<$lastPage ? $this->currPage+1: $lastPage;
        return $pages;
    }
    // +----------------------------------------------------------------------+
}

/*

$pager = new Paginate()->set( 'perPage', 25 );
if( !$query = $pager->loadQuery() ) {
    $query = new Query;
    // ... prepare query...
}
$pager->saveQuery( $query );
$data = $query->select();
$info = $pager->getPages();
 */