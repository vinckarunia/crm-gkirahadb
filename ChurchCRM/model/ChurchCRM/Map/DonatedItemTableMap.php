<?php

namespace ChurchCRM\model\ChurchCRM\Map;

use ChurchCRM\model\ChurchCRM\DonatedItem;
use ChurchCRM\model\ChurchCRM\DonatedItemQuery;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\InstancePoolTrait;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\DataFetcher\DataFetcherInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\RelationMap;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Map\TableMapTrait;


/**
 * This class defines the structure of the 'donateditem_di' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class DonatedItemTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'ChurchCRM.model.ChurchCRM.Map.DonatedItemTableMap';

    /**
     * The default database name for this class
     */
    const DATABASE_NAME = 'default';

    /**
     * The table name for this class
     */
    const TABLE_NAME = 'donateditem_di';

    /**
     * The related Propel class for this table
     */
    const OM_CLASS = '\\ChurchCRM\\model\\ChurchCRM\\DonatedItem';

    /**
     * A class that can be returned by this tableMap
     */
    const CLASS_DEFAULT = 'ChurchCRM.model.ChurchCRM.DonatedItem';

    /**
     * The total number of columns
     */
    const NUM_COLUMNS = 15;

    /**
     * The number of lazy-loaded columns
     */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    const NUM_HYDRATE_COLUMNS = 15;

    /**
     * the column name for the di_ID field
     */
    const COL_DI_ID = 'donateditem_di.di_ID';

    /**
     * the column name for the di_item field
     */
    const COL_DI_ITEM = 'donateditem_di.di_item';

    /**
     * the column name for the di_FR_ID field
     */
    const COL_DI_FR_ID = 'donateditem_di.di_FR_ID';

    /**
     * the column name for the di_donor_ID field
     */
    const COL_DI_DONOR_ID = 'donateditem_di.di_donor_ID';

    /**
     * the column name for the di_buyer_ID field
     */
    const COL_DI_BUYER_ID = 'donateditem_di.di_buyer_ID';

    /**
     * the column name for the di_multibuy field
     */
    const COL_DI_MULTIBUY = 'donateditem_di.di_multibuy';

    /**
     * the column name for the di_title field
     */
    const COL_DI_TITLE = 'donateditem_di.di_title';

    /**
     * the column name for the di_description field
     */
    const COL_DI_DESCRIPTION = 'donateditem_di.di_description';

    /**
     * the column name for the di_sellprice field
     */
    const COL_DI_SELLPRICE = 'donateditem_di.di_sellprice';

    /**
     * the column name for the di_estprice field
     */
    const COL_DI_ESTPRICE = 'donateditem_di.di_estprice';

    /**
     * the column name for the di_minimum field
     */
    const COL_DI_MINIMUM = 'donateditem_di.di_minimum';

    /**
     * the column name for the di_materialvalue field
     */
    const COL_DI_MATERIALVALUE = 'donateditem_di.di_materialvalue';

    /**
     * the column name for the di_EnteredBy field
     */
    const COL_DI_ENTEREDBY = 'donateditem_di.di_EnteredBy';

    /**
     * the column name for the di_EnteredDate field
     */
    const COL_DI_ENTEREDDATE = 'donateditem_di.di_EnteredDate';

    /**
     * the column name for the di_picture field
     */
    const COL_DI_PICTURE = 'donateditem_di.di_picture';

    /**
     * The default string format for model objects of the related table
     */
    const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        self::TYPE_PHPNAME       => array('Id', 'Item', 'FrId', 'DonorId', 'BuyerId', 'Multibuy', 'Title', 'Description', 'Sellprice', 'Estprice', 'Minimum', 'MaterialValue', 'Enteredby', 'Entereddate', 'Picture', ),
        self::TYPE_CAMELNAME     => array('id', 'item', 'frId', 'donorId', 'buyerId', 'multibuy', 'title', 'description', 'sellprice', 'estprice', 'minimum', 'materialValue', 'enteredby', 'entereddate', 'picture', ),
        self::TYPE_COLNAME       => array(DonatedItemTableMap::COL_DI_ID, DonatedItemTableMap::COL_DI_ITEM, DonatedItemTableMap::COL_DI_FR_ID, DonatedItemTableMap::COL_DI_DONOR_ID, DonatedItemTableMap::COL_DI_BUYER_ID, DonatedItemTableMap::COL_DI_MULTIBUY, DonatedItemTableMap::COL_DI_TITLE, DonatedItemTableMap::COL_DI_DESCRIPTION, DonatedItemTableMap::COL_DI_SELLPRICE, DonatedItemTableMap::COL_DI_ESTPRICE, DonatedItemTableMap::COL_DI_MINIMUM, DonatedItemTableMap::COL_DI_MATERIALVALUE, DonatedItemTableMap::COL_DI_ENTEREDBY, DonatedItemTableMap::COL_DI_ENTEREDDATE, DonatedItemTableMap::COL_DI_PICTURE, ),
        self::TYPE_FIELDNAME     => array('di_ID', 'di_item', 'di_FR_ID', 'di_donor_ID', 'di_buyer_ID', 'di_multibuy', 'di_title', 'di_description', 'di_sellprice', 'di_estprice', 'di_minimum', 'di_materialvalue', 'di_EnteredBy', 'di_EnteredDate', 'di_picture', ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        self::TYPE_PHPNAME       => array('Id' => 0, 'Item' => 1, 'FrId' => 2, 'DonorId' => 3, 'BuyerId' => 4, 'Multibuy' => 5, 'Title' => 6, 'Description' => 7, 'Sellprice' => 8, 'Estprice' => 9, 'Minimum' => 10, 'MaterialValue' => 11, 'Enteredby' => 12, 'Entereddate' => 13, 'Picture' => 14, ),
        self::TYPE_CAMELNAME     => array('id' => 0, 'item' => 1, 'frId' => 2, 'donorId' => 3, 'buyerId' => 4, 'multibuy' => 5, 'title' => 6, 'description' => 7, 'sellprice' => 8, 'estprice' => 9, 'minimum' => 10, 'materialValue' => 11, 'enteredby' => 12, 'entereddate' => 13, 'picture' => 14, ),
        self::TYPE_COLNAME       => array(DonatedItemTableMap::COL_DI_ID => 0, DonatedItemTableMap::COL_DI_ITEM => 1, DonatedItemTableMap::COL_DI_FR_ID => 2, DonatedItemTableMap::COL_DI_DONOR_ID => 3, DonatedItemTableMap::COL_DI_BUYER_ID => 4, DonatedItemTableMap::COL_DI_MULTIBUY => 5, DonatedItemTableMap::COL_DI_TITLE => 6, DonatedItemTableMap::COL_DI_DESCRIPTION => 7, DonatedItemTableMap::COL_DI_SELLPRICE => 8, DonatedItemTableMap::COL_DI_ESTPRICE => 9, DonatedItemTableMap::COL_DI_MINIMUM => 10, DonatedItemTableMap::COL_DI_MATERIALVALUE => 11, DonatedItemTableMap::COL_DI_ENTEREDBY => 12, DonatedItemTableMap::COL_DI_ENTEREDDATE => 13, DonatedItemTableMap::COL_DI_PICTURE => 14, ),
        self::TYPE_FIELDNAME     => array('di_ID' => 0, 'di_item' => 1, 'di_FR_ID' => 2, 'di_donor_ID' => 3, 'di_buyer_ID' => 4, 'di_multibuy' => 5, 'di_title' => 6, 'di_description' => 7, 'di_sellprice' => 8, 'di_estprice' => 9, 'di_minimum' => 10, 'di_materialvalue' => 11, 'di_EnteredBy' => 12, 'di_EnteredDate' => 13, 'di_picture' => 14, ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, )
    );

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var string[]
     */
    protected $normalizedColumnNameMap = [

        'Id' => 'DI_ID',
        'DonatedItem.Id' => 'DI_ID',
        'id' => 'DI_ID',
        'donatedItem.id' => 'DI_ID',
        'DonatedItemTableMap::COL_DI_ID' => 'DI_ID',
        'COL_DI_ID' => 'DI_ID',
        'di_ID' => 'DI_ID',
        'donateditem_di.di_ID' => 'DI_ID',
        'Item' => 'DI_ITEM',
        'DonatedItem.Item' => 'DI_ITEM',
        'item' => 'DI_ITEM',
        'donatedItem.item' => 'DI_ITEM',
        'DonatedItemTableMap::COL_DI_ITEM' => 'DI_ITEM',
        'COL_DI_ITEM' => 'DI_ITEM',
        'di_item' => 'DI_ITEM',
        'donateditem_di.di_item' => 'DI_ITEM',
        'FrId' => 'DI_FR_ID',
        'DonatedItem.FrId' => 'DI_FR_ID',
        'frId' => 'DI_FR_ID',
        'donatedItem.frId' => 'DI_FR_ID',
        'DonatedItemTableMap::COL_DI_FR_ID' => 'DI_FR_ID',
        'COL_DI_FR_ID' => 'DI_FR_ID',
        'di_FR_ID' => 'DI_FR_ID',
        'donateditem_di.di_FR_ID' => 'DI_FR_ID',
        'DonorId' => 'DI_DONOR_ID',
        'DonatedItem.DonorId' => 'DI_DONOR_ID',
        'donorId' => 'DI_DONOR_ID',
        'donatedItem.donorId' => 'DI_DONOR_ID',
        'DonatedItemTableMap::COL_DI_DONOR_ID' => 'DI_DONOR_ID',
        'COL_DI_DONOR_ID' => 'DI_DONOR_ID',
        'di_donor_ID' => 'DI_DONOR_ID',
        'donateditem_di.di_donor_ID' => 'DI_DONOR_ID',
        'BuyerId' => 'DI_BUYER_ID',
        'DonatedItem.BuyerId' => 'DI_BUYER_ID',
        'buyerId' => 'DI_BUYER_ID',
        'donatedItem.buyerId' => 'DI_BUYER_ID',
        'DonatedItemTableMap::COL_DI_BUYER_ID' => 'DI_BUYER_ID',
        'COL_DI_BUYER_ID' => 'DI_BUYER_ID',
        'di_buyer_ID' => 'DI_BUYER_ID',
        'donateditem_di.di_buyer_ID' => 'DI_BUYER_ID',
        'Multibuy' => 'DI_MULTIBUY',
        'DonatedItem.Multibuy' => 'DI_MULTIBUY',
        'multibuy' => 'DI_MULTIBUY',
        'donatedItem.multibuy' => 'DI_MULTIBUY',
        'DonatedItemTableMap::COL_DI_MULTIBUY' => 'DI_MULTIBUY',
        'COL_DI_MULTIBUY' => 'DI_MULTIBUY',
        'di_multibuy' => 'DI_MULTIBUY',
        'donateditem_di.di_multibuy' => 'DI_MULTIBUY',
        'Title' => 'DI_TITLE',
        'DonatedItem.Title' => 'DI_TITLE',
        'title' => 'DI_TITLE',
        'donatedItem.title' => 'DI_TITLE',
        'DonatedItemTableMap::COL_DI_TITLE' => 'DI_TITLE',
        'COL_DI_TITLE' => 'DI_TITLE',
        'di_title' => 'DI_TITLE',
        'donateditem_di.di_title' => 'DI_TITLE',
        'Description' => 'DI_DESCRIPTION',
        'DonatedItem.Description' => 'DI_DESCRIPTION',
        'description' => 'DI_DESCRIPTION',
        'donatedItem.description' => 'DI_DESCRIPTION',
        'DonatedItemTableMap::COL_DI_DESCRIPTION' => 'DI_DESCRIPTION',
        'COL_DI_DESCRIPTION' => 'DI_DESCRIPTION',
        'di_description' => 'DI_DESCRIPTION',
        'donateditem_di.di_description' => 'DI_DESCRIPTION',
        'Sellprice' => 'DI_SELLPRICE',
        'DonatedItem.Sellprice' => 'DI_SELLPRICE',
        'sellprice' => 'DI_SELLPRICE',
        'donatedItem.sellprice' => 'DI_SELLPRICE',
        'DonatedItemTableMap::COL_DI_SELLPRICE' => 'DI_SELLPRICE',
        'COL_DI_SELLPRICE' => 'DI_SELLPRICE',
        'di_sellprice' => 'DI_SELLPRICE',
        'donateditem_di.di_sellprice' => 'DI_SELLPRICE',
        'Estprice' => 'DI_ESTPRICE',
        'DonatedItem.Estprice' => 'DI_ESTPRICE',
        'estprice' => 'DI_ESTPRICE',
        'donatedItem.estprice' => 'DI_ESTPRICE',
        'DonatedItemTableMap::COL_DI_ESTPRICE' => 'DI_ESTPRICE',
        'COL_DI_ESTPRICE' => 'DI_ESTPRICE',
        'di_estprice' => 'DI_ESTPRICE',
        'donateditem_di.di_estprice' => 'DI_ESTPRICE',
        'Minimum' => 'DI_MINIMUM',
        'DonatedItem.Minimum' => 'DI_MINIMUM',
        'minimum' => 'DI_MINIMUM',
        'donatedItem.minimum' => 'DI_MINIMUM',
        'DonatedItemTableMap::COL_DI_MINIMUM' => 'DI_MINIMUM',
        'COL_DI_MINIMUM' => 'DI_MINIMUM',
        'di_minimum' => 'DI_MINIMUM',
        'donateditem_di.di_minimum' => 'DI_MINIMUM',
        'MaterialValue' => 'DI_MATERIALVALUE',
        'DonatedItem.MaterialValue' => 'DI_MATERIALVALUE',
        'materialValue' => 'DI_MATERIALVALUE',
        'donatedItem.materialValue' => 'DI_MATERIALVALUE',
        'DonatedItemTableMap::COL_DI_MATERIALVALUE' => 'DI_MATERIALVALUE',
        'COL_DI_MATERIALVALUE' => 'DI_MATERIALVALUE',
        'di_materialvalue' => 'DI_MATERIALVALUE',
        'donateditem_di.di_materialvalue' => 'DI_MATERIALVALUE',
        'Enteredby' => 'DI_ENTEREDBY',
        'DonatedItem.Enteredby' => 'DI_ENTEREDBY',
        'enteredby' => 'DI_ENTEREDBY',
        'donatedItem.enteredby' => 'DI_ENTEREDBY',
        'DonatedItemTableMap::COL_DI_ENTEREDBY' => 'DI_ENTEREDBY',
        'COL_DI_ENTEREDBY' => 'DI_ENTEREDBY',
        'di_EnteredBy' => 'DI_ENTEREDBY',
        'donateditem_di.di_EnteredBy' => 'DI_ENTEREDBY',
        'Entereddate' => 'DI_ENTEREDDATE',
        'DonatedItem.Entereddate' => 'DI_ENTEREDDATE',
        'entereddate' => 'DI_ENTEREDDATE',
        'donatedItem.entereddate' => 'DI_ENTEREDDATE',
        'DonatedItemTableMap::COL_DI_ENTEREDDATE' => 'DI_ENTEREDDATE',
        'COL_DI_ENTEREDDATE' => 'DI_ENTEREDDATE',
        'di_EnteredDate' => 'DI_ENTEREDDATE',
        'donateditem_di.di_EnteredDate' => 'DI_ENTEREDDATE',
        'Picture' => 'DI_PICTURE',
        'DonatedItem.Picture' => 'DI_PICTURE',
        'picture' => 'DI_PICTURE',
        'donatedItem.picture' => 'DI_PICTURE',
        'DonatedItemTableMap::COL_DI_PICTURE' => 'DI_PICTURE',
        'COL_DI_PICTURE' => 'DI_PICTURE',
        'di_picture' => 'DI_PICTURE',
        'donateditem_di.di_picture' => 'DI_PICTURE',
    ];

    /**
     * Initialize the table attributes and columns
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws PropelException
     */
    public function initialize()
    {
        // attributes
        $this->setName('donateditem_di');
        $this->setPhpName('DonatedItem');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\ChurchCRM\\model\\ChurchCRM\\DonatedItem');
        $this->setPackage('ChurchCRM.model.ChurchCRM');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('di_ID', 'Id', 'SMALLINT', true, 9, null);
        $this->addColumn('di_item', 'Item', 'VARCHAR', true, 32, null);
        $this->addColumn('di_FR_ID', 'FrId', 'SMALLINT', true, 9, null);
        $this->addColumn('di_donor_ID', 'DonorId', 'SMALLINT', true, 9, 0);
        $this->addColumn('di_buyer_ID', 'BuyerId', 'SMALLINT', true, 9, 0);
        $this->addColumn('di_multibuy', 'Multibuy', 'SMALLINT', true, 1, 0);
        $this->addColumn('di_title', 'Title', 'VARCHAR', true, 128, null);
        $this->addColumn('di_description', 'Description', 'LONGVARCHAR', false, null, null);
        $this->addColumn('di_sellprice', 'Sellprice', 'DECIMAL', false, 8, null);
        $this->addColumn('di_estprice', 'Estprice', 'DECIMAL', false, 8, null);
        $this->addColumn('di_minimum', 'Minimum', 'DECIMAL', false, 8, null);
        $this->addColumn('di_materialvalue', 'MaterialValue', 'DECIMAL', false, 8, null);
        $this->addColumn('di_EnteredBy', 'Enteredby', 'SMALLINT', true, 5, 0);
        $this->addColumn('di_EnteredDate', 'Entereddate', 'DATE', true, null, null);
        $this->addColumn('di_picture', 'Picture', 'LONGVARCHAR', false, null, null);
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
    } // buildRelations()

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return string The primary key hash of the row
     */
    public static function getPrimaryKeyHashFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        // If the PK cannot be derived from the row, return NULL.
        if ($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] === null) {
            return null;
        }

        return null === $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] || is_scalar($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)]) || is_callable([$row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)], '__toString']) ? (string) $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] : $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
    }

    /**
     * Retrieves the primary key from the DB resultset row
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, an array of the primary key columns will be returned.
     *
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return mixed The primary key of the row
     */
    public static function getPrimaryKeyFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        return (int) $row[
            $indexType == TableMap::TYPE_NUM
                ? 0 + $offset
                : self::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)
        ];
    }

    /**
     * The class that the tableMap will make instances of.
     *
     * If $withPrefix is true, the returned path
     * uses a dot-path notation which is translated into a path
     * relative to a location on the PHP include_path.
     * (e.g. path.to.MyClass -> 'path/to/MyClass.php')
     *
     * @param boolean $withPrefix Whether or not to return the path with the class name
     * @return string path.to.ClassName
     */
    public static function getOMClass($withPrefix = true)
    {
        return $withPrefix ? DonatedItemTableMap::CLASS_DEFAULT : DonatedItemTableMap::OM_CLASS;
    }

    /**
     * Populates an object of the default type or an object that inherit from the default.
     *
     * @param array  $row       row returned by DataFetcher->fetch().
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                 One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return array           (DonatedItem object, last column rank)
     */
    public static function populateObject($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        $key = DonatedItemTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = DonatedItemTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + DonatedItemTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = DonatedItemTableMap::OM_CLASS;
            /** @var DonatedItem $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            DonatedItemTableMap::addInstanceToPool($obj, $key);
        }

        return array($obj, $col);
    }

    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @param DataFetcherInterface $dataFetcher
     * @return array
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function populateObjects(DataFetcherInterface $dataFetcher)
    {
        $results = array();

        // set the class once to avoid overhead in the loop
        $cls = static::getOMClass(false);
        // populate the object(s)
        while ($row = $dataFetcher->fetch()) {
            $key = DonatedItemTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = DonatedItemTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var DonatedItem $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                DonatedItemTableMap::addInstanceToPool($obj, $key);
            } // if key exists
        }

        return $results;
    }
    /**
     * Add all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be added to the select list and only loaded
     * on demand.
     *
     * @param Criteria $criteria object containing the columns to add.
     * @param string   $alias    optional table alias
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function addSelectColumns(Criteria $criteria, $alias = null)
    {
        if (null === $alias) {
            $criteria->addSelectColumn(DonatedItemTableMap::COL_DI_ID);
            $criteria->addSelectColumn(DonatedItemTableMap::COL_DI_ITEM);
            $criteria->addSelectColumn(DonatedItemTableMap::COL_DI_FR_ID);
            $criteria->addSelectColumn(DonatedItemTableMap::COL_DI_DONOR_ID);
            $criteria->addSelectColumn(DonatedItemTableMap::COL_DI_BUYER_ID);
            $criteria->addSelectColumn(DonatedItemTableMap::COL_DI_MULTIBUY);
            $criteria->addSelectColumn(DonatedItemTableMap::COL_DI_TITLE);
            $criteria->addSelectColumn(DonatedItemTableMap::COL_DI_DESCRIPTION);
            $criteria->addSelectColumn(DonatedItemTableMap::COL_DI_SELLPRICE);
            $criteria->addSelectColumn(DonatedItemTableMap::COL_DI_ESTPRICE);
            $criteria->addSelectColumn(DonatedItemTableMap::COL_DI_MINIMUM);
            $criteria->addSelectColumn(DonatedItemTableMap::COL_DI_MATERIALVALUE);
            $criteria->addSelectColumn(DonatedItemTableMap::COL_DI_ENTEREDBY);
            $criteria->addSelectColumn(DonatedItemTableMap::COL_DI_ENTEREDDATE);
            $criteria->addSelectColumn(DonatedItemTableMap::COL_DI_PICTURE);
        } else {
            $criteria->addSelectColumn($alias . '.di_ID');
            $criteria->addSelectColumn($alias . '.di_item');
            $criteria->addSelectColumn($alias . '.di_FR_ID');
            $criteria->addSelectColumn($alias . '.di_donor_ID');
            $criteria->addSelectColumn($alias . '.di_buyer_ID');
            $criteria->addSelectColumn($alias . '.di_multibuy');
            $criteria->addSelectColumn($alias . '.di_title');
            $criteria->addSelectColumn($alias . '.di_description');
            $criteria->addSelectColumn($alias . '.di_sellprice');
            $criteria->addSelectColumn($alias . '.di_estprice');
            $criteria->addSelectColumn($alias . '.di_minimum');
            $criteria->addSelectColumn($alias . '.di_materialvalue');
            $criteria->addSelectColumn($alias . '.di_EnteredBy');
            $criteria->addSelectColumn($alias . '.di_EnteredDate');
            $criteria->addSelectColumn($alias . '.di_picture');
        }
    }

    /**
     * Remove all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be removed as they are only loaded on demand.
     *
     * @param Criteria $criteria object containing the columns to remove.
     * @param string   $alias    optional table alias
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function removeSelectColumns(Criteria $criteria, $alias = null)
    {
        if (null === $alias) {
            $criteria->removeSelectColumn(DonatedItemTableMap::COL_DI_ID);
            $criteria->removeSelectColumn(DonatedItemTableMap::COL_DI_ITEM);
            $criteria->removeSelectColumn(DonatedItemTableMap::COL_DI_FR_ID);
            $criteria->removeSelectColumn(DonatedItemTableMap::COL_DI_DONOR_ID);
            $criteria->removeSelectColumn(DonatedItemTableMap::COL_DI_BUYER_ID);
            $criteria->removeSelectColumn(DonatedItemTableMap::COL_DI_MULTIBUY);
            $criteria->removeSelectColumn(DonatedItemTableMap::COL_DI_TITLE);
            $criteria->removeSelectColumn(DonatedItemTableMap::COL_DI_DESCRIPTION);
            $criteria->removeSelectColumn(DonatedItemTableMap::COL_DI_SELLPRICE);
            $criteria->removeSelectColumn(DonatedItemTableMap::COL_DI_ESTPRICE);
            $criteria->removeSelectColumn(DonatedItemTableMap::COL_DI_MINIMUM);
            $criteria->removeSelectColumn(DonatedItemTableMap::COL_DI_MATERIALVALUE);
            $criteria->removeSelectColumn(DonatedItemTableMap::COL_DI_ENTEREDBY);
            $criteria->removeSelectColumn(DonatedItemTableMap::COL_DI_ENTEREDDATE);
            $criteria->removeSelectColumn(DonatedItemTableMap::COL_DI_PICTURE);
        } else {
            $criteria->removeSelectColumn($alias . '.di_ID');
            $criteria->removeSelectColumn($alias . '.di_item');
            $criteria->removeSelectColumn($alias . '.di_FR_ID');
            $criteria->removeSelectColumn($alias . '.di_donor_ID');
            $criteria->removeSelectColumn($alias . '.di_buyer_ID');
            $criteria->removeSelectColumn($alias . '.di_multibuy');
            $criteria->removeSelectColumn($alias . '.di_title');
            $criteria->removeSelectColumn($alias . '.di_description');
            $criteria->removeSelectColumn($alias . '.di_sellprice');
            $criteria->removeSelectColumn($alias . '.di_estprice');
            $criteria->removeSelectColumn($alias . '.di_minimum');
            $criteria->removeSelectColumn($alias . '.di_materialvalue');
            $criteria->removeSelectColumn($alias . '.di_EnteredBy');
            $criteria->removeSelectColumn($alias . '.di_EnteredDate');
            $criteria->removeSelectColumn($alias . '.di_picture');
        }
    }

    /**
     * Returns the TableMap related to this object.
     * This method is not needed for general use but a specific application could have a need.
     * @return TableMap
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function getTableMap()
    {
        return Propel::getServiceContainer()->getDatabaseMap(DonatedItemTableMap::DATABASE_NAME)->getTable(DonatedItemTableMap::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this tableMap class.
     */
    public static function buildTableMap()
    {
        $dbMap = Propel::getServiceContainer()->getDatabaseMap(DonatedItemTableMap::DATABASE_NAME);
        if (!$dbMap->hasTable(DonatedItemTableMap::TABLE_NAME)) {
            $dbMap->addTableObject(new DonatedItemTableMap());
        }
    }

    /**
     * Performs a DELETE on the database, given a DonatedItem or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or DonatedItem object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param  ConnectionInterface $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
     public static function doDelete($values, ConnectionInterface $con = null)
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(DonatedItemTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \ChurchCRM\model\ChurchCRM\DonatedItem) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(DonatedItemTableMap::DATABASE_NAME);
            $criteria->add(DonatedItemTableMap::COL_DI_ID, (array) $values, Criteria::IN);
        }

        $query = DonatedItemQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            DonatedItemTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                DonatedItemTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the donateditem_di table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(ConnectionInterface $con = null)
    {
        return DonatedItemQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a DonatedItem or Criteria object.
     *
     * @param mixed               $criteria Criteria or DonatedItem object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(DonatedItemTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from DonatedItem object
        }

        if ($criteria->containsKey(DonatedItemTableMap::COL_DI_ID) && $criteria->keyContainsValue(DonatedItemTableMap::COL_DI_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.DonatedItemTableMap::COL_DI_ID.')');
        }


        // Set the correct dbName
        $query = DonatedItemQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

} // DonatedItemTableMap
// This is the static code needed to register the TableMap for this table with the main Propel class.
//
DonatedItemTableMap::buildTableMap();
