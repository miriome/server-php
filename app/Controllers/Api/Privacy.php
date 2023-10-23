<?php

namespace App\Controllers\Api;

use App\Controllers\Api\Base;

use App\Models\Api\ReportsModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Privacy extends Base
{
    protected $_reportsModel;
    public function __construct()
    {

        $this->_reportsModel = new ReportsModel();
    }

    public function index()
    {
        //
    }

    public function reportContent()
    {

        $userId = $this->request->user->userId;
        $reportType = $this->request->getPost('report_type');
        $type_id = $this->request->getPost('type_id');
        $reason = $this->request->getPost('reason');

        $fields = [
            "report_type" => $reportType,
            "type_id" => $type_id,
            "reason" => $reason,
            "reported_by" => $userId,
        ];
        try {
            $this->_reportsModel->add($fields);
        } catch (DatabaseException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $response = [
                    'status' => false,
                    'message' => "You have already reported this $reportType",
                ];
                return $this->response->setJSON($response);
            } else {
                // Other database error
                echo "An error occurred!";
            }
        }


        $response = [
            'status' => true,
            'data' => [],

        ];



        return $this->response->setJSON($response);
    }



}
