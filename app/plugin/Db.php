<?php
namespace Utm\Plugin;
/**
 * Classe de gestion de base MySql avec MySqli
 */
class Db extends \Utm\CorePlugin
{
    protected $m_oDb;       // Object connection a la base
    protected $m_oResult;   // Objet resultat
    protected $m_aConf;     // Configuration de la class DB
    public $m_iRequestNb;   // Nombre de requete effectuées par la classe
    public $m_aRequest;     // Stock les requetes dans un tableau pour debug ou analyse
    public $m_oDataTable;   // implemente un objet datable pour le rendu de tableaux

    /**
     * réagit on onstart du framework, initialise la connexion mysqli
     */
    public function onStart()
    {
        $this->m_iRequestNb = 0;
        $this->m_aConf = \Utm\Core::$config['db'];
        $this->m_oDb = new \mysqli($this->m_aConf['host'], $this->m_aConf['username'], $this->m_aConf['passwd'], $this->m_aConf['dbname'], $this->m_aConf['port']);
        //definition du charset de la connexion
        if (!$this->m_oDb->set_charset($this->m_aConf['charset'])) {
            throw new \Exception('Le jeu de caractère \'' . $this->m_aConf['charset'] . '\' n\'a pas pu etre defini pour la connexion à la base (Valeur courante :utf8, latin1, etc.) : ' . __METHOD__);
        }
    }
    
    /**
     * destructeur
     */
    public function __destruct()
    {
        $this->m_oDb->close();
    }
    
    /**
     * Lance une requete
     * @param string $p_rSql Requete
     * @return array Tableau de resultats
     */
    public function query($p_rSql) 
    {
        $this->m_oResult = $this->m_oDb->query($p_rSql);
        $this->m_aRequest[] = $p_rSql;
        $return = array();// tableau permettant de retourner l'ensemble des résultat
        if ($this->m_oResult) {
            if (strchr($p_rSql,"SELECT ")) {
                while ($row = $this->m_oResult->fetch_array(MYSQLI_ASSOC)){
                    $return[] = $row;
                }
                $this->m_oResult->close();
            }
            $this->m_iRequestNb++;
            return $return;
        } else {
            throw new \Exception('Classe DB : erreur sql "'.$p_rSql.'" : ' . __METHOD__);
        }
    }
    
    /**
     * Lance une requete et recupere la premiere ligne
     * @param string $p_rSql Requete
     * @return object Contient en variable membre le retour de chaque champ de la requete
     */
    public function queryRow($p_rSql)
    {
        $this->m_oResult = $this->m_oDb->query($p_rSql);
        $this->m_aRequest[] = $p_rSql;
        if ($this->m_oResult) {
            $this->m_iRequestNb++;
            return $this->m_oResult->fetch_object();
        } else {
            throw new \Exception('Classe DB : erreur sql : ' . __METHOD__);
        }
    }
    
    /**
     * Lance une requete et recupere la premiere ligne
     * @param string $p_rSql Requete
     * @return object Contient en variable membre le retour de chaque champ de la requete
     */
    public function queryRowToArray($p_rSql)
    {
        $this->m_oResult = $this->m_oDb->query($p_rSql);
        $this->m_aRequest[] = $p_rSql;
        if ($this->m_oResult) {
            $this->m_iRequestNb++;
            return $this->m_oResult->fetch_array(MYSQLI_ASSOC);
        } else {
            throw new \Exception('Classe DB : erreur sql : ' . __METHOD__);
        }
    }
    
    /**
     * Lance une requete et recupere le premier enregistrement de la
     * premiere ligne.
     * @param string $p_rSql Requete
     * @return array Tableau indexé de la premiere ligne ou FALSE
     */
    public function queryOne($p_rSql)
    {
        $this->m_oResult = $this->m_oDb->query($p_rSql);
        $this->m_aRequest[] = $p_rSql;
        if ($this->m_oResult) {
            $this->m_iRequestNb++;
            $row = $this->m_oResult->fetch_row();
            return $row[0];
        } else {
            throw new \Exception('Classe DB : erreur sql "'.$p_rSql.'" : ' . __METHOD__);
        }
    }
    
    /**
     * Renvoi le dernier id inséré en base
     */
    public function lastId()
    {
        return $this->m_oDb->insert_id;
    }
    
    /**
     * Renvoi le nombre d'enregistrement dans le resultat de la requete
     */
    public function rowCount()
    {
        return $this->m_oResult->num_rows;
    }
    
    /**
     * Encrypte un chaine selon un hash MD5 ou SHA1 auquel une graine à été
     * ajouté.
     * @param string $p_sString Mot de passe
     * @param string $p_sEnc Encodage utilisé
     *
     * @return string Chaine encodé
     */
    public function enc($p_sString, $p_sEnc = 'MD5')
    {
        if (strtoupper($p_sEnc) == 'SHA' || strtoupper($p_sEnc) == 'SHA1') {
            return SHA1($this->m_aConf['seed'].$p_sString);
        } else {
            return MD5($this->m_aConf['seed'].$p_sString);
        }
    }
    
    /**
     * Echappe une chaine de caractère
     * @param string $p_sString Chaine à échapper
     *
     * @return string Chaine echappée
     */
    public function quote($p_sString)
    {
        if (!is_array($p_sString)){
            return $this->m_oDb->real_escape_string($p_sString);
        } else {
            return false;
        }
    }
    
    public function dataTable($p_sTable, $p_aField, $p_sKey, $p_sBaseUrl, $p_sSortBy, $p_sSortWay = 'ASC', $p_iDataLimit = 20, $p_sWhere = '1')
    {
        $nb = $this->dataTableCount($p_sTable, $p_sKey, $p_sWhere);
        if (0 === $nb) {
            // no data then we return
            return;
        }
        
        $pager = $this->dataTableInitPager($nb, $p_iDataLimit);
        $return = $this->dataTableQuery($p_sTable, $p_aField, $p_sSortBy, $p_sSortWay, $p_sWhere, $pager);
        $return['pager'] = $this->dataTablePager($pager['currentPage'], $pager['nbPage'], $p_sBaseUrl);
        return $return;
    }
    
    public function dataTableQuery($p_sTable, $p_aField, $p_sSortBy, $p_sSortWay, $p_sWhere, $p_aPager)
    {
        $l_sSortBy = filter_input(INPUT_GET, 'sortby') ?? $p_sSortBy;
        $l_sSortWay = filter_input(INPUT_GET, 'sortway') ?? $p_sSortWay;
        
        $l_sSqlField = '';
        foreach ($p_aField as $field) {
            $return['field'][$field] = [
                'key' => $field,
                'sort' => $l_sSortBy ==  $field ? true : false,
                'sortway' => $l_sSortBy ==  $field ? $l_sSortWay : 'ASC',
            ];
            $l_sSqlField .= ' ' . $field . ',';
        }
        $l_sSqlField = rtrim($l_sSqlField, ',');        
        
        $l_sSql = 'SELECT '.$l_sSqlField.'  
            FROM `'.$p_sTable.'` 
            WHERE '.$p_sWhere.'   
            ORDER BY '.$l_sSortBy.' '.$l_sSortWay.' '. $p_aPager['limit'];
        $return['data'] = $this->query($l_sSql);
        return $return;
    }
    
    public function dataTableCount($p_sTable, $p_sKey, $p_sWhere = '1'):int
    {
        $l_sSql = 'SELECT count(`'.$p_sKey.'`)
            FROM `' . $p_sTable . '`
            WHERE '.$p_sWhere.' ';
        return (int)$this->queryOne($l_sSql);
    }
    
    public function dataTableInitPager($p_iNb, $p_iDataLimit = 20):array
    {
        $l_iPage = filter_input(INPUT_GET, 'page') ?? 1;
        $offset = $p_iDataLimit * ($l_iPage - 1);
        
        return [
            'limit' => " LIMIT $offset, $p_iDataLimit",
            'nbPage' => (int)ceil($p_iNb / $p_iDataLimit),
            'currentPage' => $l_iPage,
        ];
    }
    
    public function dataTablePager(int $p_iCurrentPage, int $p_iNbPage, string $p_sUrl):array
    {
        $return = [
            'prev' => null,
            'page' => null,
            'next' => null,
        ];
        
        if ($p_iNbPage > 1) {
            $return['prev']['url'] = ($p_iCurrentPage === 1) ? 
                 "$p_sUrl&page=1" : "$p_sUrl&page=" . ($p_iCurrentPage - 1);
            $return['prev']['disabled'] = ($p_iCurrentPage !== 1) ? false : true;

            for ($i=1;$i<=$p_iNbPage; $i++) {
                $return['page'][$i] = [
                    'label' => $i,
                    'url' => "$p_sUrl&page=$i",
                    'active' => ($p_iCurrentPage === $i) ? true : false,
                ];
            }
            
            $return['next']['url'] = ($p_iCurrentPage !== $p_iNbPage) ? 
                "$p_sUrl&page=".($p_iCurrentPage + 1) : "$p_sUrl&page=".$p_iNbPage;
            $return['next']['disabled'] = ($p_iCurrentPage !== $p_iNbPage) ? false : true;
        }
        return $return;
    }
}