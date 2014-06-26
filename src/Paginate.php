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

    protected function getKey( $data, $key )
    {
        return array_key_exists( $key, $data ) ? $data[$key] : null;
    }

    protected function setSaveId() {
        $this->saveID = 'Paginated-'.md5( $this->currUri );
    }

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
        $this->currUri = $uri ?: $this->getKey( $_SERVER, 'REQUEST_URI' );
        $this->setSaveId();
        if( $limit = $this->getKey( $_GET, $this->limiter ) ) {
            $this->perPage = $limit;
        }
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
        if( !$page ) $page = $this->getKey( $_GET, $this->pager );
        if( !$page ) return null;
        if( !isset($this->session[$this->saveID]) ) return null;
        
        $this->currPage = $page;
        $this->query   = $this->session[$this->saveID]['query'];
        $this->perPage = $this->session[$this->saveID]['perPage'];
        $this->setPageToQuery( $page );
        return $this->query;
    }

    /**
     * @param Query $query
     * @return $this
     */
    public function setQuery( $query )
    {
        $this->query = $query;
        $this->setPageToQuery( $this->currPage );
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
    protected function setPageToQuery( $page )
    {
        $this->query->limit( $this->perPage );
        $this->query->offset( $this->perPage * ($page - 1) );
    }

    /**
     * @return $this
     */
    public function queryTotal() {
        $this->total = $this->query->count();
        return $this->total;
    }

    /**
     * @return array
     */
    public function queryPage() {
        return $this->query->select();
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