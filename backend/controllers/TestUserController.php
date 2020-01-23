<?php

namespace backend\controllers;

use Yii;
use yii\data\Pagination;
use common\models\TestUser;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * TestUserController implements the CRUD actions for TestUser model.
 */
class TestUserController extends BaseController
{
	public $layout = "lte_main";

    /**
     * Lists all TestUser models.
     * @return mixed
     */
    public function actionIndex()
    {
        $query = TestUser::find();
         $querys = Yii::$app->request->get('query');
         if(empty($querys)== false && count($querys) > 0){
            $condition = "";
            $parame = array();
            foreach($querys as $key=>$value){
                $value = trim($value);
                if(empty($value) == false){
                    $parame[":{$key}"]=$value;
                    if(empty($condition) == true){
                        $condition = " {$key}=:{$key} ";
                    }
                    else{
                        $condition = $condition . " AND {$key}=:{$key} ";
                    }
                }
            }
            if(count($parame) > 0){
                $query = $query->where($condition, $parame);
            }
        }

        $pagination = new Pagination([
            'totalCount' =>$query->count(), 
            'pageSize' => '10', 
            'pageParam'=>'page', 
            'pageSizeParam'=>'per-page']
        );
        
        $orderby = Yii::$app->request->get('orderby', '');
        if(empty($orderby) == false){
            $query = $query->orderBy($orderby);
        }
        
        
        $models = $query
        ->offset($pagination->offset)
        ->limit($pagination->limit)
        ->all();
        return $this->render('index', [
            'models'=>$models,
            'pages'=>$pagination,
            'query'=>$querys,
        ]);
    }

    /**
     * Displays a single TestUser model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $data = $model->getAttributes();
        
		if(empty($model->domain_account) == false){
            $model->domain_account = \yii\helpers\Url::base(true). $model->domain_account;
        }
        if(empty($data['domain_account']) == false){
            $data['initialPreview'] = array(\yii\helpers\Url::base(true).$data['domain_account']);
            $data['initialPreviewConfig'] = array(array('url'=>\yii\helpers\Url::toRoute('test-user/delete-file').'&id='.$data['id']));
        }
        else{
            $data['initialPreview'] = array();
            $data['initialPreviewConfig'] = array();
        }
        	
        
        return $this->asJson($data);

    }

    /**
     * Creates a new TestUser model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new TestUser();
        if ($model->load(Yii::$app->request->post())) {
        
             if(empty($model->last_ip) == false && is_array($model->last_ip) == true){
               $model->last_ip = json_encode($model->last_ip); 
             }
              if(empty($model->is_online) == true){
                  $model->is_online = 'n';
              }
              if(empty($model->status) == true){
                  $model->status = 10;
              }
              $model->create_user = Yii::$app->user->identity->uname;
              $model->create_date = date('Y-m-d H:i:s');
              $model->update_user = Yii::$app->user->identity->uname;
              $model->update_date = date('Y-m-d H:i:s');        
            if($model->validate() == true && $model->save()){
                $msg = array('errno'=>0, 'msg'=>'保存成功');
                return $this->asJson($msg);
            }
            else{
                $msg = array('errno'=>2, 'data'=>$model->getErrors());
                return $this->asJson($msg);
            }
        } else {
            $msg = array('errno'=>2, 'msg'=>'数据出错');
            return $this->asJson($msg);
        }
    }

    /**
     * Updates an existing TestUser model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate()
    {
        $id = Yii::$app->request->post('id');
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post())) {
        
             if(empty($model->last_ip) == false && is_array($model->last_ip) == true){
               $model->last_ip = json_encode($model->last_ip); 
             }
             if(empty($model->is_online) == true){
                 $model->is_online = 'n';
             }
             if(empty($model->status) == true){
                 $model->status = 10;
             }
              $model->update_user = Yii::$app->user->identity->uname;
              $model->update_date = date('Y-m-d H:i:s');        
        
            if($model->validate() == true && $model->save()){
                $msg = array('errno'=>0, 'msg'=>'保存成功');
                return $this->asJson($msg);
            }
            else{
                $msg = array('errno'=>2, 'data'=>$model->getErrors());
                return $this->asJson($msg);
            }
        } else {
            $msg = array('errno'=>2, 'msg'=>'数据出错');
            return $this->asJson($msg);
        }
    
    }

    /**
     * Deletes an existing TestUser model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete(array $ids)
    {
        if(count($ids) > 0){
            $c = TestUser::deleteAll(['in', 'id', $ids]);
            return $this->asJson(array('errno'=>0, 'data'=>$c, 'msg'=>json_encode($ids)));
        }
        else{
            return $this->asJson(array('errno'=>2, 'msg'=>''));
        }
    }

		   	       /**
                 * 上传图片
                 */
                public function actionUploadImages(){
                    $webroot = Yii::$app->basePath.'/web';
                    $images = \yii\web\UploadedFile::getInstancesByName('domain_account_file');
                    $result = array();
                    foreach($images as $file){
                        $fileName = uniqid() . '.'.$file->extension;
                        $filePath = '/resource/images/'.date('Ymd/H');
                        $savePath = $webroot.$filePath;
                        if(file_exists($savePath)  == false){
                            mkdir($savePath,0755,true);
                            chmod($savePath,0755);
                        }
                        $savePath = "$savePath/{$fileName}";
                        $file->saveAs($savePath);
                        $filePath = "$filePath/$fileName";
                        $result = array(
                            'image'=>$filePath,
                            'url'=>\yii\helpers\Url::base(true).$filePath
                        );
                    }
                    return $this->asJson($result);
                }
                
                    /**
                     * 删除图片
                     */
                    public function actionDeleteFile(){
                        $id = Yii::$app->request->get('id');
                        $model = $this->findModel($id);
                        $filePath = $model->domain_account;
                        $webroot = Yii::$app->basePath.'/web';
                        $imagePath = Yii::$app->basePath.'/web/resource/images/';
                        $file = $webroot.$filePath;
                        $result = array('errno'=>0, 'msg'=>'删除成功');
                        if(strpos($filePath, $imagePath) !== false){
                            if(is_file($file) == true){
                                if(unlink($file) == false){
                                    $result = array('errno'=>2, 'msg'=>'删除失败');
                                }
                            }
                        }
                        return $this->asJson($result);
                    }
	
	 

    /**
     * Finds the TestUser model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return TestUser the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TestUser::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
