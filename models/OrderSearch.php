<?php

namespace amilna\yes\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use amilna\yes\models\Order;

/**
 * OrderSearch represents the model behind the search form about `amilna\yes\models\Order`.
 */
class OrderSearch extends Order
{

	
	/*public $confirmationsId;*/
	public $customerName;
	public $customerAdminName;
	/*public $salesId;*/

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'customer_id', 'status', 'isdel'], 'integer'],
            [['reference', 'customerName','customerAdminName','total', 'data', 'time', 'complete_reference', 'complete_time', 'log'/*, 'confirmationsId', 'customerId', 'salesId'*/], 'safe'],
        ];
    }
    
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),[
            'customerName' => Yii::t('app', 'Customer'),                                    
            'customerAdminName' => Yii::t('app', 'Customer'),                                    
        ]);
    }

	public static function find()
	{
		return parent::find()->where([Order::tableName().'.isdel' => 0]);
	}

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

	private function queryString($fields)
	{		
		$params = [];
		foreach ($fields as $afield)
		{
			$field = $afield[0];
			$tab = isset($afield[1])?$afield[1]:false;			
			if (!empty($this->$field))
			{				
				if (substr($this->$field,0,2) == "< " || substr($this->$field,0,2) == "> " || substr($this->$field,0,2) == "<=" || substr($this->$field,0,2) == ">=" || substr($this->$field,0,2) == "<>") 
				{					
					array_push($params,[str_replace(" ","",substr($this->$field,0,2)), "lower(".($tab?$tab.".":"").$field.")", strtolower(trim(substr($this->$field,2)))]);
				}
				else
				{					
					array_push($params,["like", "lower(".($tab?$tab.".":"").$field.")", strtolower($this->$field)]);
				}				
			}
		}	
		return $params;
	}	
	
	private function queryNumber($fields)
	{		
		$params = [];
		foreach ($fields as $afield)
		{
			$field = $afield[0];
			$tab = isset($afield[1])?$afield[1]:false;			
			if (!empty($this->$field))
			{				
				$number = explode(" ",trim($this->$field));							
				if (count($number) == 2)
				{									
					if (in_array($number[0],['>','>=','<','<=','<>']) && is_numeric($number[1]))
					{
						array_push($params,[$number[0], ($tab?$tab.".":"").$field, $number[1]]);	
					}
				}
				elseif (count($number) == 3)
				{															
					if (is_numeric($number[0]) && is_numeric($number[2]))
					{
						array_push($params,['>=', ($tab?$tab.".":"").$field, $number[0]]);		
						array_push($params,['<=', ($tab?$tab.".":"").$field, $number[2]]);		
					}
				}
				elseif (count($number) == 1)
				{					
					if (is_numeric($number[0]))
					{
						array_push($params,['=', ($tab?$tab.".":"").$field, str_replace(["<",">","="],"",$number[0])]);		
					}	
				}
			}
		}	
		return $params;
	}
	
	private function queryTime($fields)
	{		
		$params = [];
		foreach ($fields as $afield)
		{
			$field = $afield[0];
			$tab = isset($afield[1])?$afield[1]:false;			
			if (!empty($this->$field))
			{				
				$time = explode(" - ",$this->$field);			
				if (count($time) > 1)
				{								
					array_push($params,[">=", "concat('',".($tab?$tab.".":"").$field.")", $time[0]]);	
					array_push($params,["<=", "concat('',".($tab?$tab.".":"").$field.")", $time[1]." 24:00:00"]);
				}
				else
				{
					if (substr($time[0],0,2) == "< " || substr($time[0],0,2) == "> " || substr($time[0],0,2) == "<=" || substr($time[0],0,2) == ">=" || substr($time[0],0,2) == "<>") 
					{					
						array_push($params,[str_replace(" ","",substr($time[0],0,2)), "concat('',".($tab?$tab.".":"").$field.")", trim(substr($time[0],2))]);
					}
					else
					{					
						array_push($params,["like", "concat('',".($tab?$tab.".":"").$field.")", $time[0]]);
					}
				}	
			}
		}	
		return $params;
	}

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
       $query = $this->find();
        
                
        $query->joinWith(['customer'/*'confirmations', 'customer', 'sales'*/]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        
        $dataProvider->sort->attributes['customerAdminName'] = [			
			'asc' => [Customer::tableName().'.name' => SORT_ASC],
			'desc' => [Customer::tableName().'.name' => SORT_DESC],
		];
		
		$dataProvider->sort->attributes['customerName'] = [			
			'asc' => [Customer::tableName().'.name' => SORT_ASC],
			'desc' => [Customer::tableName().'.name' => SORT_DESC],
		];
		
        /* uncomment to sort by relations table on respective column
		$dataProvider->sort->attributes['confirmationsId'] = [			
			'asc' => ['{{%confirmations}}.id' => SORT_ASC],
			'desc' => ['{{%confirmations}}.id' => SORT_DESC],
		];		
		$dataProvider->sort->attributes['salesId'] = [			
			'asc' => ['{{%sales}}.id' => SORT_ASC],
			'desc' => ['{{%sales}}.id' => SORT_DESC],
		];*/

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }				
		
        $params = self::queryNumber([['id',$this->tableName()],['customer_id'],['total'],['status'],['isdel']/*['id','{{%confirmations}}'],['id','{{%customer}}'],['id','{{%sales}}']*/]);
		foreach ($params as $p)
		{
			$query->andFilterWhere($p);
		}
        $params = self::queryString([['reference'],['data'],['complete_reference'],['log']/*['id','{{%confirmations}}'],['id','{{%customer}}'],['id','{{%sales}}']*/]);
		foreach ($params as $p)
		{
			$query->andFilterWhere($p);
		}
        $params = self::queryTime([['time'],['complete_time']/*['id','{{%confirmations}}'],['id','{{%customer}}'],['id','{{%sales}}']*/]);
		foreach ($params as $p)
		{
			$query->andFilterWhere($p);
		}
		
		$query->andFilterWhere(['like',"lower(concat(".Customer::tableName().".name,".Customer::tableName().".email,".Customer::tableName().".phones))",strtolower($this->customerAdminName)]);
		if ($this->customerName)
		{
			$query->andFilterWhere(['like',"lower(concat(',',".Customer::tableName().".name,',',".Customer::tableName().".email,',',".Customer::tableName().".phones,','))",strtolower(",".$this->customerName.",")]);
		}
		
        return $dataProvider;
    }
}
