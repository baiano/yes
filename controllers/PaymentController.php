<?php

namespace amilna\yes\controllers;

use Yii;
use amilna\yes\models\Payment;
use amilna\yes\models\PaymentSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PaymentController implements the CRUD actions for Payment model.
 */
class PaymentController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Payment models.
     * @params string $format, array $arraymap, string $term
     * @return mixed
     */
    public function actionIndex($format= false,$arraymap= false,$term = false)
    {
        $searchModel = new PaymentSearch();        
        $req = Yii::$app->request->queryParams;
        if ($term) { $req[basename(str_replace("\\","/",get_class($searchModel)))]["term"] = $term;}        
        $dataProvider = $searchModel->search($req);				

        if ($format == 'json')
        {
			$model = [];
			foreach ($dataProvider->getModels() as $d)
			{
				$obj = $d->attributes;
				if ($arraymap)
				{
					$map = explode(",",$arraymap);
					if (count($map) == 1)
					{
						$obj = (isset($d[$arraymap])?$d[$arraymap]:null);
					}
					else
					{
						$obj = [];					
						foreach ($map as $a)
						{
							$k = explode(":",$a);						
							$v = (count($k) > 1?$k[1]:$k[0]);
							$obj[$k[0]] = ($v == "Obj"?json_encode($d->attributes):(isset($d->$v)?$d->$v:null));
						}
					}
				}
				
				if ($term)
				{
					if (!in_array($obj,$model))
					{
						array_push($model,$obj);
					}
				}
				else
				{	
					array_push($model,$obj);
				}
			}			
			return \yii\helpers\Json::encode($model);	
		}
		else
		{
			return $this->render('index', [
				'searchModel' => $searchModel,
				'dataProvider' => $dataProvider,
			]);
		}	
    }

    /**
     * Displays a single Payment model.
     * @param integer $id
     * @additionalParam string $format
     * @return mixed
     */
    public function actionView($id,$format= false)
    {
        $model = $this->findModel($id);
        
        if ($format == 'json')
        {
			return \yii\helpers\Json::encode($model);	
		}
		else
		{
			return $this->render('view', [
				'model' => $model,
			]);
		}        
    }

    /**
     * Creates a new Payment model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Payment();
		$model->isdel = 0;
		
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Payment model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Payment model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {        
		$model = $this->findModel($id);        
        $model->isdel = 1;
        $model->save();
        //$model->delete(); //this will true delete
        
        return $this->redirect(['index']);
    }

    /**
     * Finds the Payment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Payment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Payment::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
