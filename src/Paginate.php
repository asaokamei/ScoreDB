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
    //  set up the pagination.
    // +----------------------------------------------------------------------+
    /**
     * @param array|null $session
     * @param string|null $uri
     */
    public function __construct( &$session=null, $uri=null )
    {
        if( is_null( $session ) ) {
            $this->session = $_SESSION;
        } else {
            $this->session = &$session;
        }
        $this->currUri = $uri ?: $_SERVER['REQUEST_URI'];
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
            $this->$key = $value;
        }
        return $this;
    }

    // +----------------------------------------------------------------------+
    //  query management.
    // +----------------------------------------------------------------------+
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
        $this->query   = $this->session[$this->saveID]['query'];
        $this->perPage = $this->session[$this->saveID]['perPage'];
        $this->queryPage( $page );
        $this->perPage = $this->queryGetLimit();
        return $this->query;
    }

    /**
     * @param Query $query
     * @return $this
     */
    public function setQuery( $query )
    {
        $this->query = $query;
        $this->queryPage( $this->currPage );
    }

    /**
     * @return $this
     */
    public function saveQuery()
    {
        $this->session[$this->saveID] = [
            'perPage' => $this->perPage,
            'query'   => clone( $this->query ),
        ];
        return $this;
    }

    /**
     * @param int $page
     */
    protected function queryPage( $page )
    {
        $this->query->page( $page, $this->perPage );
    }

    /**
     * @return int
     */
    protected function queryGetLimit() 
    {
        return $this->query->getLimit();
    }
    
    /**
     * @return $this
     */
    public function countQuery() {
        $this->total = $this->query->count();
        return $this;
    }

    // +----------------------------------------------------------------------+
    //  public methods for constructing pagination info.
    // +----------------------------------------------------------------------+
    /**
     * @return int
     */
    public function getTotal() {
        return $this->total;
    }

    /**
     * @return int
     */
    public function getCurrPage() {
        return $this->currPage;
    }

    // +----------------------------------------------------------------------+
    //  preparing for pagination list. Yep, this should go any other class.
    // +----------------------------------------------------------------------+
    /**
     * @param int $numLinks
     * @return array
     */
    function getPages( $numLinks = 5 )
    {
        $pages = [
            'found' => $this->getTotal(),
            'curr_page' => $this->getCurrPage(),
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
    $pager->setQuery( $query );
}
$pager->countQuery();
$pager->saveQuery();
$data = $query->select();
$info = $pager->getPages();
 */