<?php
namespace Utm\Model;

class Player extends \Utm\CoreModel
{
    protected $table = 'player';
    protected $table_prefix = '';
    
    public function getById($p_iPlayerId) 
    {
        $Db = $this->getPlugin('Db');
        if ((int) $p_iPlayerId > 0) {
            $l_sSql = 'SELECT *
                FROM `'.$this->table_prefix.$this->table.'`
                WHERE id_player = "' . (int) $p_iPlayerId .'"';
            return $Db->queryRow($l_sSql);
        }
    }
    
    public function getListing($p_iDataLimit = 20) {        
        
        $Db = $this->getPlugin('Db');
        $dbtable = $this->table_prefix.$this->table;
        $fieldArray = ['id_player', 'pseudo', 'ville', 'nom', 'prenom',
            'genre', 'email',
        ];
        $dbkey = 'id_player';
        $sortBy = 'nom';
        $sortWay = 'ASC';
        $baseUrl = '?mod=utmbo&ctrl=index';
                
        return $Db->dataTable($dbtable, $fieldArray, $dbkey, $baseUrl, $sortBy, $sortWay, $p_iDataLimit);
    }
}
