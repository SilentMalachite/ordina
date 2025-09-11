<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\DataManagementController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Models\User;
use App\Models\ClosingDate;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $dashboardController;
    protected $userController;
    protected $dataController;
    protected $settingsController;

    public function __construct()
    {
        $this->middleware('permission:system-manage');
        $this->dashboardController = new AdminDashboardController();
        $this->userController = new UserManagementController();
        $this->dataController = new DataManagementController();
        $this->settingsController = new SystemSettingsController();
    }

    public function index()
    {
        return $this->dashboardController->index();
    }

    // ユーザー管理関連
    public function users()
    {
        return $this->userController->index();
    }

    public function createUser()
    {
        return $this->userController->create();
    }

    public function storeUser(Request $request)
    {
        return $this->userController->store($request);
    }

    public function editUser(User $user)
    {
        return $this->userController->edit($user);
    }

    public function updateUser(Request $request, User $user)
    {
        return $this->userController->update($request, $user);
    }

    public function destroyUser(User $user)
    {
        return $this->userController->destroy($user);
    }

    // データ管理関連
    public function dataManagement()
    {
        return $this->dataController->index();
    }

    public function backupData()
    {
        return $this->dataController->backup();
    }

    public function clearData(Request $request)
    {
        return $this->dataController->clear($request);
    }

    public function downloadBackup($filename)
    {
        return $this->dataController->downloadBackup($filename);
    }

    // システム設定関連
    public function systemSettings()
    {
        return $this->settingsController->index();
    }

    public function closingDates()
    {
        return $this->settingsController->closingDates();
    }

    public function createClosingDate()
    {
        return $this->settingsController->createClosingDate();
    }

    public function storeClosingDate(Request $request)
    {
        return $this->settingsController->storeClosingDate($request);
    }

    public function destroyClosingDate(ClosingDate $closingDate)
    {
        return $this->settingsController->destroyClosingDate($closingDate);
    }

    public function systemLogs()
    {
        return $this->settingsController->systemLogs();
    }


}