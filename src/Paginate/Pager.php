<?php
namespace WScore\ScoreDB\Paginate;

use WScore\ScoreDB\Query;

/**
 * Class Pager
 * @package WScore\ScoreDB\Paginate
 *
 * keys to set:
 *  - pager   : $_GET variable name to set the page number.
 *  - limiter : $_GET variable to set perPage number.
 *  - perPage : default perPage number.
 *
 *
 */
class Pager
{
    /**
     * @var Query
     */
    protected $query;

    protected $pager = '_page';
    
    protected $limiter = '_limit';
    
    protected $perPage = 20;
    
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
     */
    public function __construct( &$session=null )
    {
        if( is_null( $session ) ) {
            $this->session = $_SESSION;
        } else {
            $this->session = &$session;
        }
        $this->setSaveId();
        if( $limit = $this->getKey( $_GET, $this->limiter ) ) {
            $this->perPage = $limit;
        }
    }

    /**
     * @param string $uri
     * @return ToHtml
     */
    public function html( $uri=null )
    {
        $toHtml = new ToHtml( $uri );
        $toHtml->setPager($this);
        return $toHtml;
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

    protected function getKey( $data, $key )
    {
        return array_key_exists( $key, $data ) ? $data[$key] : null;
    }

    protected function setSaveId() {
        $this->saveID = 'Paginated-'.md5( $_SERVER["SCRIPT_FILENAME"] );
    }

    // +----------------------------------------------------------------------+
    //  query management.
    // +----------------------------------------------------------------------+
    /**
     * if $page is specified (either as argument or in $_GET['_limit'],
     * returns the query from the session with perPage value set as limit.
     *
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
        $this->setPageToQuery();
        return $this->query;
    }

    /**
     * set the brand new query to the paginate.
     *
     * @param Query $query
     * @return $this
     */
    public function setQuery( $query )
    {
        $this->query = $query;
        $this->setPageToQuery();
    }

    /**
     * saves the query object and perPage value to the session.
     *
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
     * sets limit and offset to the query.
     */
    protected function setPageToQuery()
    {
        $this->query->limit( $this->perPage );
        $this->query->offset( $this->perPage * ($this->currPage - 1) );
    }

    /**
     * query for the total using count(). sets the total as the found data.
     *
     * @return $this
     */
    public function queryTotal() {
        $this->total = $this->query->count();
        return $this->total;
    }

    /**
     * queries data for the page.
     *
     * @return array
     */
    public function queryPage() {
        return $this->query->select();
    }

    /**
     * queries data for the page, with extra 1 data to check
     * if there is more data. if there are more data than the
     * perPage, it sets the total as extra 1.
     *
     * returns data only the perPage number of data.
     * Todo: NOT TESTED!!!
     *
     * @return array
     */
    public function queryPageWithNext()
    {
        $this->perPage++;
        $this->setPageToQuery();
        $data    = $this->queryPage();
        if( count( $data ) >= $this->perPage ) {
            $this->total = $this->currPage * $this->perPage + 1;
            $data = array_slice( $data, 0, $this->perPage-1 );
        } else {
            $this->total = ($this->currPage - 1 ) * $this->perPage + count( $data );
        }
        return $data;
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

    /**
     * @return int|null
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * @return string
     */
    public function getPageKey()
    {
        return $this->pager;
    }

    // +----------------------------------------------------------------------+
}
