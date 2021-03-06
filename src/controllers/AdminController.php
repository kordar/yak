<?php

namespace kordar\yak\controllers;

use Yii;
use yii\base\UserException;
use yii\helpers\Html;
use kordar\yak\models\rbac\AssignModel;
use kordar\yak\models\admin\EditForm;
use kordar\yak\models\admin\SignupForm;
use kordar\yak\models\admin\User as Admin;
use kordar\yak\models\admin\UserSearch as AdminSearch;
use yii\web\NotFoundHttpException;

/**
 * AdminController implements the CRUD actions for Admin model.
 * @item *:管理员管理
 * @item create:创建管理员
 * @item delete:删除管理员
 * @item update:更新管理员
 * @item index:管理员列表
 * @item view:管理员详情
 * @item assign:管理员授权
 */
class AdminController extends YakController
{

    /**
     * Lists all Admin models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AdminSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->renderTpl('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Admin model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->renderTpl('view', [
            'model' => Admin::find()->select('*')->addSelect(Admin::extFieldsByCase())->where(['id'=>$id])->one()
        ]);
    }


    /**
     * Creates a new Admin model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SignupForm();

        if ($model->load(Yii::$app->request->post()) && $user = $model->signup()) {
            return $this->redirect(['view', 'id' => $user->id]);
        }

        return $this->renderTpl('create', ['model' => $model]);
    }

    /**
     * Updates an existing Admin model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = EditForm::findOne($id);

        if ($model->load(Yii::$app->request->post()) && $model->edit()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->renderTpl('update', [
                'model' => $model,
            ]);
        }
    }

    // 授权
    public function actionAssign($id, $name)
    {
        $model = new AssignModel();

        if (Yii::$app->request->isPost) {

            if ($model->load(Yii::$app->request->post(), '') && $model->setChildrenToUser($id)) {
                Yii::$app->session->setFlash('success', Html::tag('b', '[' . $name . ']') . Yii::t('yak', 'Permission assignment is successful'));
                return $this->redirect(['index']);
            }
            Yii::$app->session->setFlash('warning', Yii::t('yak', 'Permission assignment failed'));
        }

        return $this->renderTpl('assign', [
            'userId' => $id, 'name' => $name
        ]);
    }

    /**
     * Deletes an existing Admin model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->type == Admin::TYPE_SUPER) {
            throw new UserException(Yii::t('yak', 'superuser does not allow deletion'));
        }

        $model->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Admin model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Admin the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Admin::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
