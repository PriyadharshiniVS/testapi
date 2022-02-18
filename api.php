<?php
error_reporting(0);

class Api_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    //  New Api Models for Department portal

    //user login --->department table

    public function Login($data)
    {
        $login = "SELECT User_Code, Department_Code,HOD_Code,Department_Name FROM user WHERE User_Name='" . $data->Username . "' AND Password='" . $data->Password . "'";
        $query = $this->db->query($login);
        $result = $query->result();
        return $result;
    }

    ///////////////////////Supplier Master/////////////////

    //Get supplier details

    public function getSupplier($data)
    {
        $get = "SELECT Supplier_Code AS supplierCode
		,Supplier_Name AS supplierName
		,Supplier_Address AS supplierAddress
		,Supplier_City AS supplierCity  
		,Supplier_State AS supplierState   
		,Supplier_Pincode AS supplierPin   
		,Supplier_Email AS supplierEmail   
		,Supplier_Phone AS supplierPhone   
		,Supplier_GST AS supplierGst   
		,Created_By AS createdBy
		,Created_Date AS createdDate 
		,Type AS type   

		FROM suppliermaster WHERE Status='A' ORDER BY supplierCode  DESC";
        $query = $this->db->query($get);
        $result = $query->result_array();

        return $result;
    }

    //Post Supplier details

    public function postSupplier($data)
    {


        $insert = "INSERT INTO suppliermaster(
				Supplier_Name
				,Supplier_Address   
				,Supplier_City
				,Supplier_State
				,Supplier_Pincode
				,Supplier_Email
				,Supplier_Phone
				,Supplier_GST
				,Type
				,Status
				)

		 VALUES(
			'" . $data->supplierName . "'
		   ,'" . $data->supplierAddress . "'
		   ,'" . $data->supplierCity . "'
		   ,'" . $data->supplierState . "'
		       , $data->supplierPin 
		   ,'" . $data->supplierEmail . "'
		       , $data->supplierPhone 
		   ,'" . $data->supplierGst . "' 
		     ,'" . $data->type . "'
		   ,'A'

		 );";

        $query = $this->db->query($insert);
        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    //Put supplier Details

    public function putSupplier($data)
    {
        $insert = "UPDATE suppliermaster

		           SET   Supplier_Name=	'" . $data->supplierName . "'
						,Supplier_Address='" . $data->supplierAddress . "'
						,Supplier_City=  '" . $data->supplierCity . "'
						,Supplier_State= '" . $data->supplierState . "'
						,Supplier_Pincode=  $data->supplierPin 
						,Supplier_Email= '" . $data->supplierEmail . "'
						,Supplier_Phone=$data->supplierPhone  
						,Supplier_GST='" . $data->supplierGst . "'   
						,Type='" . $data->type . "'
						,Status='A'
				   WHERE Supplier_Code=$data->supplierCode";
        $query = $this->db->query($insert);
        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    //delete function for Supplier master

    public function delSupplier($data)
    {
        $delete = "UPDATE suppliermaster SET Status='D' WHERE Supplier_Code=$data->supplierCode";

        $query = $this->db->query($delete);
        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    ///////////////////////Staff Details/////////////////

    //Get staff details

    public function getStaff($data)
    {
        $get = "SELECT Staff_Code,Staff_Name FROM staff WHERE Department_Code=$data->departmentCode";
        $query = $this->db->query($get);
        $result = $query->result_array();

        return $result;
    }

    //Post staff details

    public function postStaff($data)
    {

        $insert = "INSERT INTO staff(
			           Staff_Name
			    	  ,Department_Code)		  
		 VALUES(
			'" . $data->staffName . "'
		       ,$data->departmentCode );";

        $query = $this->db->query($insert);
        $result = $query->result_array();
        return $result;
    }

    ///////////////////////Returnable Gate Pass Details/////////////////

    // Get returnable gate pass header

    public function getRetGpHeader($data)
    {
        $get = "SELECT ret_gp_header.*,staff.Staff_Name,suppliermaster.Supplier_Name,department.Department_Name
		 FROM (((ret_gp_header
		 INNER JOIN department ON ret_gp_header.Department_Code=department.Department_Code)
		 INNER JOIN staff ON ret_gp_header.Staff_Code=staff.Staff_Code)
		 INNER JOIN suppliermaster ON ret_gp_header.Supplier_Code=suppliermaster.Supplier_Code)
		 WHERE department.Department_Code=$data->departmentCode ; ";

        $query = $this->db->query($get);
        $result = $query->result();
        return $result;
    }


    //Insert Returnable Gate Pass Header

    public function postRetGpHeader($data)
    {


        if (empty($data->lineEntries) != 1) {
            $insert = "INSERT INTO ret_gp_header(
				Department_Code
				,Supplier_Code
				,Transport_Mode
				,Transport_Company
				,Vehicle_No
				,No_of_Items
				,Purpose
				,App_Cost
				,Staff_Code
				,Gate_Pass
				,Status
				)

		 VALUES(
			    $data->departmentCode
			   ,$data->supplierCode
		  ,'" . $data->transportMode . "'
		  ,'" . $data->transportCompany . "'
		  ,'" . $data->vehicleNo . "'
			  , $data->noOfItems 
		  ,'" . $data->purpose . "'
			   ,$data->appCost 
			   ,$data->staffCode
		  ,'" . $data->gatePass . "'
			   ,'A'
		 );";


            $query = $this->db->query($insert);

            $RegIdDesc = "SELECT RGP_Id from ret_gp_header ORDER BY RGP_Id DESC LIMIT 1;";
            $selecQuery = $this->db->query($RegIdDesc);
            $selecres = $selecQuery->result_array();

            $rgpId = $selecres[0]['RGP_Id'];

            if ($selecres != '') {
                $result = $this->postRetGpLine($data, $rgpId);
            }
        } else {
            print_r("Error");
            exit;
        }
        return $result;
    }

    //Insert Returnable Gate Pass Line

    public function postRetGpLine($data, $rgpId)
    {

        if ($data->lineEntries != '') {
            for ($i = 0; $i < count($data->lineEntries); $i++) {
                $Description = $data->lineEntries[$i]->description;
                $UOM = $data->lineEntries[$i]->unit;
                $Quantity = $data->lineEntries[$i]->quantity;

                if ($Description != '' && $UOM != '' && $Quantity != '') {

                    $insert = "INSERT INTO ret_gp_line(
						RGP_Id,
				       Description
				       ,UOM
				       ,Quantity
					  ,Supplier_Code )
				 VALUES(
					     $rgpId
				   ,'" . $data->lineEntries[$i]->description . "'
			      ,'" .  $data->lineEntries[$i]->unit . "'
				  , '" . $data->lineEntries[$i]->quantity . "'
				  , '" . $data->supplierCode . "'

				     
					);";

                    if ($insert != "") {
                        $query = $this->db->query($insert);
                    } else {
                        print_r('failure');
                    }
                }
            }
        } else {
            print_r("error");
        }
        $result = $query->result_array();
        return $result;
    }

    //delete function for Returnable Gatepass
    public function delRetGpHeader($data)
    {
        $delete = "UPDATE ret_gp_header SET Status='D' WHERE RGP_Id=$data->rgpId";
        $query = $this->db->query($delete);
        $result = $query->result_array();
        return $result;
    }


    ///////////////////////Item Return Entry Details/////////////////

    // Get item entry

    public function getItemRetEntry($data)
    {

        $get = "SELECT line.*,header.RGP_Date,SUM(returnable.Recd_Qty) AS recdQty,header.Department_Code,IF((line.Quantity-SUM(returnable.Recd_Qty))=0,'notPending','pending') AS st
				FROM ret_gp_line as line 
				JOIN ret_gp_header as header ON (line.RGP_Id=header.RGP_Id)
				LEFT JOIN item_ret_entry AS returnable ON (line.Sno=returnable.Sno)
				WHERE header.RGP_Id=$data->rgpId AND header.Status='A' AND header.Gate_Pass ='Returnable' GROUP BY line.Sno;";
        $query = $this->db->query($get);
        $result = $query->result_array();
        return $result;
    }
    public function returnDataCount($data)
    {
        $count = "SELECT COUNT(RGP_Id) AS rgpId FROM item_ret_entry WHERE Created_By=$data->depCode;";
        $res = $this->db->query($count);
        $result = $res->result_array();
        $total = $result[0]['rgpId'];
        $data = ceil($total / 10);
        return $data;
    }
    public function returnPagination($data)
    {
        $noOfPage = $data->pageNo;
        $resultPerPage = 10;
        $finalResult = ($noOfPage - 1) * $resultPerPage;
        $check = "SELECT line.Sno AS sno,
		line.RGP_Id AS rgpId,
		line.Description AS description,
		line.Quantity AS quantity,
		returnable.Created_Date AS createdDate,
		SUM(returnable.Recd_Qty) AS recdQty,header.Department_Code
		FROM ret_gp_line as line
		JOIN ret_gp_header as header ON (line.RGP_Id=header.RGP_Id)
		JOIN item_ret_entry AS returnable ON (line.Sno=returnable.Sno)
		WHERE header.Gate_Pass ='Returnable' AND header.Department_Code=$data->depCode GROUP BY line.Sno ORDER BY line.RGP_Id DESC
		LIMIT  $finalResult,$resultPerPage;";

        // print_r($check);
        // exit;
        $query = $this->db->query($check);
        $result = $query->result_array();

        return $result;
    }

    // Post item entry

    public function postItemRetEntry($data)
    {
        $insert = "INSERT INTO item_ret_entry(
			     Sno
				,RGP_Id
				,RGP_Date
				,Item_Description
				,Recd_Qty
				,Created_By
				)

		 VALUES(
			    $data->sno
			    ,$data->rgpId
		   ,'" . $data->rgpDate . "'
		  ,'" . $data->description . "'
		       ,$data->recdQty 
		   ,'" . $data->createdBy . "' 
		 );";

        // print_r($insert);
        // exit;
        $query = $this->db->query($insert);
        $result = $query->result_array();
        return $result;
    }

    public function supplierDataCount()
    {

        $count = "SELECT COUNT(Supplier_Code) AS supplierCode FROM suppliermaster WHERE Status='A'";
        $res = $this->db->query($count);
        $result = $res->result_array();
        $total = $result[0]['supplierCode'];

        $data = ceil($total / 10);

        return $data;
    }

    public function supplierPagination($data)
    {

        $noOfPage = $data->pageNo;
        $resultPerPage = 10;
        $finalResult = ($noOfPage - 1) * $resultPerPage;

        $page = "SELECT Supplier_Code AS supplierCode
		,Supplier_Name AS supplierName
		,Supplier_Address AS supplierAddress
		,Supplier_City AS supplierCity  
		,Supplier_State AS supplierState   
		,Supplier_Pincode AS supplierPin   
		,Supplier_Email AS supplierEmail   
		,Supplier_Phone AS supplierPhone   
		,Supplier_GST AS supplierGst   
		,Created_By AS createdBy 
		,Created_Date AS createdDate 
		FROM suppliermaster 
		WHERE Status='A'
		ORDER BY supplierCode DESC LIMIT $resultPerPage OFFSET $finalResult;";

        $query = $this->db->query($page);

        $result = $query->result_array();

        return $result;
    }
    public function gatePassDataCount($data)
    {
        $count = "SELECT COUNT(RGP_Id) AS rgpId FROM ret_gp_header WHERE Status='A' AND Department_Code=$data->depCode ";
        $res = $this->db->query($count);
        $result = $res->result_array();
        $total = $result[0]['rgpId'];
        $data = ceil($total / 10);

        return $data;
    }
    public function gatePassPagination($data)
    {

        $noOfPage = $data->pageNo;
        $resultPerPage = 10;
        $finalResult = ($noOfPage - 1) * $resultPerPage;


        $get = "SELECT 
		ret_gp_header.RGP_Id AS rgpId,
		ret_gp_header.Department_Code AS departmentCode,
		ret_gp_header.RGP_Date AS rgpDate,
		ret_gp_header.Supplier_Code AS supplierCode,
		ret_gp_header.Transport_Mode AS transportMode, 
		ret_gp_header.Transport_Company AS transportCompany, 
		ret_gp_header.Vehicle_No AS vehicleNo, 
		ret_gp_header.No_of_Items AS noOfItems, 
		ret_gp_header.Purpose AS purpose, 
		ret_gp_header.App_Cost AS appCost, 
		ret_gp_header.Staff_Code AS staffCode, 
		ret_gp_header.Gate_Pass AS gatePass, 
		ret_gp_header.Status AS Status,
		ret_gp_header.Approval AS approval,
		ret_gp_header.Forward AS forward,
		staff.Staff_Name AS staffName,
		suppliermaster.Supplier_Name  AS supplierName,
		department.Department_Name AS departmentName
		 FROM (((ret_gp_header
		 INNER JOIN department ON ret_gp_header.Department_Code=department.Department_Code)
		 INNER JOIN staff ON ret_gp_header.Staff_Code=staff.Staff_Code)
		 INNER JOIN suppliermaster ON ret_gp_header.Supplier_Code=suppliermaster.Supplier_Code)
		 WHERE ret_gp_header.Status = 'A' AND department.Department_Code=$data->departmentCode ORDER BY rgpId  DESC LIMIT $finalResult ,$resultPerPage;";

        $query = $this->db->query($get);
        $result = $query->result_array();

        return $result;
    }

    //get returnable gate pass line entry

    public function getRetGpLine($data)
    {
        $get = "SELECT Sno AS sNo,Description AS description,UOM AS unit,Quantity AS quantity FROM ret_gp_line WHERE RGP_Id=$data->rgpId";
        $query = $this->db->query($get);
        $result = $query->result_array();

        return $result;
    }
    public function rgpIdHeader($d)
    {

        $get = "SELECT IF(returnable.Recd_Qty = line.Quantity,0,header.RGP_Id) AS rgpId,line.Sno,IF((line.Quantity-SUM(returnable.Recd_Qty))=0,0,header.RGP_Id) AS st
		FROM ret_gp_line AS line
		LEFT JOIN item_ret_entry AS returnable ON (line.Sno=returnable.Sno)
		LEFT JOIN ret_gp_header AS header ON (line.RGP_Id=header.RGP_Id)
		WHERE header.Department_Code=$d->depCode 
		AND header.Approval='Y' 
		AND header.Gate_Pass='Returnable' 
		AND header.`Status`='A' 
		GROUP BY line.Sno HAVING st <>0;";
        $query = $this->db->query($get);
        $result = $query->result_array();
        return $result;
    }

    public function getPending()
    {
        $this->insertPending();
        $select = "SELECT * FROM pending_items";
        $query = $this->db->query($select);
        $res = $query->result_array();


        return $res;
    }
    public function insertPending()
    {
        $selectReturn = "SELECT
		line.*,line.Quantity-SUM(returnable.Recd_Qty) AS pending,SUM(returnable.Recd_Qty) AS recdQty,header.RGP_Date,supplier.Supplier_Name
		FROM ret_gp_line as line 
		JOIN ret_gp_header as header ON (line.RGP_Id=header.RGP_Id)
		JOIN item_ret_entry AS returnable ON (line.Sno=returnable.Sno)
		JOIN suppliermaster AS supplier ON (line.Supplier_Code=supplier.Supplier_Code)
		WHERE header.Gate_Pass ='Returnable' group by line.Sno ORDER BY line.Sno DESC;";
        $query = $this->db->query($selectReturn);
        $result = $query->result_array();

        $sel = "SELECT * FROM pending_items";

        $q = $this->db->query($sel);
        $res = $q->result_array();

        if (count($res) == 0) {
            for ($i = 0; $i < count($result); $i++) {
                $id = $result[$i]["RGP_Id"];
                $supplier = $result[$i]["Supplier_Name"];
                $item = $result[$i]["Description"];
                $sentQty = $result[$i]["Quantity"];
                $received = $result[$i]["recdQty"];
                $pending = $result[$i]["pending"];
                $date = $result[$i]["RGP_Date"];
                $insertPending = "INSERT INTO pending_items(RGP_Id,Supplier_Name,Item_Name,Sent_Qty,Recd_Qty,Pending_Qty,Sent_Date) VALUES(
						$id,
						'" . $supplier . "',
						'" . $item . "',
						$sentQty,
						$received,
						$pending,
					'" . $date . "');";

                $q = $this->db->query($insertPending);
            }
        } else {
            $pending = "TRUNCATE TABLE pending_items";
            $query = $this->db->query($pending);
            for ($i = 0; $i < count($result); $i++) {
                $id = $result[$i]["RGP_Id"];
                $supplier = $result[$i]["Supplier_Name"];
                $item = $result[$i]["Description"];
                $sentQty = $result[$i]["Quantity"];
                $received = $result[$i]["recdQty"];
                $pending = $result[$i]["pending"];
                $date = $result[$i]["RGP_Date"];

                $insertPending = "UPDATE pending_items SET
						Supplier_Name='" . $supplier . "',
						Item_Name= '" . $item . "',
						Sent_Qty=$sentQty,
						Recd_Qty=$received,
						Pending_Qty=$pending,
						Sent_Date='" . $date . "' WHERE RGP_Id=$id;";
                $q = $this->db->query($insertPending);
                $insertPending = "INSERT INTO pending_items(RGP_Id,Supplier_Name,Item_Name,Sent_Qty,Recd_Qty,Pending_Qty,Sent_Date) VALUES(
					$id,
					'" . $supplier . "',
					'" . $item . "',
					$sentQty,
					$received,
					$pending,
				'" . $date . "');";

                $q = $this->db->query($insertPending);
            }
        }
    }
    public function printData($data)
    {
        $header = "SELECT ret_gp_header.*,suppliermaster.*,department.* 
		 FROM ((ret_gp_header
		 INNER JOIN department ON ret_gp_header.Department_Code=department.Department_Code)
		 INNER JOIN suppliermaster ON ret_gp_header.Supplier_Code=suppliermaster.Supplier_Code)
		 WHERE RGP_Id=$data->gatePassId  ";
        $query = $this->db->query($header);
        $headerResult = $query->result_array();

        $line = "SELECT * FROM ret_gp_line
		 WHERE RGP_Id=$data->gatePassId  ";
        $query = $this->db->query($line);
        $lineResult = $query->result_array();

        $print = array($headerResult, $lineResult);
        return $print;
    }

    public function approval($data)
    {
        if ($data->forward == 'Y') {

            $forward = "UPDATE ret_gp_header SET Forward='Y' WHERE RGP_Id=$data->rgpId; ";
            $query = $this->db->query($forward);
        }
        if ($data->accept == 'Y') {

            $accept = "UPDATE ret_gp_header SET Approval='Y' WHERE RGP_Id=$data->rgpId;";
            $query = $this->db->query($accept);
        } elseif ($data->accept == 'N') {

            $decline = "UPDATE ret_gp_header SET Forward='N' WHERE RGP_Id=$data->rgpId;";
            $query = $this->db->query($decline);
        }

        return "true";
    }


    //Update Returnable Gate Pass Header

    public function updateRetGpHeader($data)
    {


        if (empty($data->lineEntries) != 1) {

            $update = "UPDATE ret_gp_header SET
		Department_Code= $data->departmentCode
		,Supplier_Code=$data->supplierCode
		,Transport_Mode='" . $data->transportMode . "'
		,Transport_Company='" . $data->transportCompany . "'
		,Vehicle_No='" . $data->vehicleNo . "'
		,No_of_Items= $data->noOfItems 
		,Purpose='" . $data->purpose . "'
		,App_Cost=$data->appCost 
		,Staff_Code=$data->staffCode
		,Gate_Pass='" . $data->gatePass . "'
		,Status='A'
		 WHERE RGP_Id=$data->rgpId; ";
            $query = $this->db->query($update);

            $reg_id = $data->rgpId;
            if ($reg_id != '') {
                $result = $this->updateRetGpLine($data);
            }
        } else {
            print_r("Error");
            exit;
        }
        return $result;
    }

    //Update Returnable Gate Pass Line

    public function updateRetGpLine($data)
    {
        $rgpId = $data->rgpId;
        $spCode = $data->supplierCode;
        $select = "SELECT Supplier_Code AS supplierCode FROM ret_gp_header WHERE RGP_Id=$data->rgpId ";
        $selHead = $this->db->query($select);
        $spCodeRes = $selHead->result_array();
        $ifNewSpCode = $spCodeRes[0]["supplierCode"];

        if ($data->lineEntries != '') {
            for ($i = 0; $i < count($data->lineEntries); $i++) {
                $Description = $data->lineEntries[$i]->description;
                $UOM = $data->lineEntries[$i]->unit;
                $Quantity = $data->lineEntries[$i]->quantity;
                $Sno = $data->lineEntries[$i]->sNo;
                if ($rgpId != '' && $Sno == '') {
                    $insert = "INSERT INTO ret_gp_line(
						RGP_Id,
				       Description
				       ,UOM
				       ,Quantity
					  ,Supplier_Code )
				 VALUES(
					     $rgpId
				   ,'" . $data->lineEntries[$i]->description . "'
			      ,'" .  $data->lineEntries[$i]->unit . "'
				  , '" . $data->lineEntries[$i]->quantity . "'
				  ,      $spCode
					);";

                    if ($insert != "") {
                        $query = $this->db->query($insert);
                    } else {
                        print_r('failure');
                    }
                }
                if ($Description != '' && $UOM != '' && $Quantity != '' && $Sno != '') {

                    $line = "UPDATE ret_gp_line SET 
	                     	 Description= '" . $Description . "'
							,UOM='" . $UOM . "'
							,Quantity= $Quantity
							,Supplier_Code=$ifNewSpCode
							  WHERE Sno=$Sno; ";
                    if ($line != "") {
                        $query = $this->db->query($line);
                    } else {
                        print_r('failure');
                    }
                }
            }
        } else {
            print_r("error");
        }
        $result = $query->result_array();
        return $result;
    }


    //Inventory//
    //ItemMaster//

    public function selectItemMaster($data)
    {
        $select = "SELECT Item_id AS itemId,
		Item_Code AS itemCode,
		Item_Description AS itemDescription,
		Item_Description1 AS itemDescription1,
		UOM AS unit,
		Min_Order_Qty AS minOrderQty ,
		Chemistry AS chemistry,
		COE AS coe,
		Physics AS physics,
		Weaving AS weaving,
		Created_By AS createdBy,
		Created_Date AS createdDate,
		Modified_By AS modifiedBy,
		Modified_Date AS modifiedDate,
		Status AS status,
		Expiry_Date_Check AS expiryDateCheck FROM item_master WHERE Status='A'  ORDER BY itemId  DESC";

        $query = $this->db->query($select);
        $res = $query->result_array();
        return $res;
    }

    public function insertItemMaster($data)
    {
        $insert = "INSERT INTO item_master(
				
				 Item_Code
				,Item_Description
				,Item_Description1
				,UOM
				,Min_Order_Qty
				,Created_By
				,Modified_By
				,Modified_Date
				,Status
				,Expiry_Date_Check
				)

		 VALUES(
			     
		    '" . $data->itemCode . "'
		   ,'" . $data->itemDescription . "'
		   ,'" . $data->itemDescription1 . "'
		       ,'" . $data->unit . "' 
		       , $data->minOrderQty
		       , $data->createdBy 
		       , $data->modifiedBy 
		   ,'" . $data->modifiedDate . "'
		   ,'A'
		   ,'" . $data->expiryDateCheck . "' );";
        if ($query = $this->db->query($insert)) {

            print_r("success");
        } else {
            print_r("failure");
        }

        $result = $query->result_array();

        return $result;
    }
    public function updateItemMaster($data)
    {
        $update = "UPDATE item_master

		           SET   Item_id=$data->itemId
				   	    ,Item_Code='" . $data->itemCode . "'
						,Item_Description='" . $data->itemDescription . "'
						,Item_Description1= '" . $data->itemDescription1 . "'
						,UOM='" . $data->unit . "' 
						,Min_Order_Qty= $data->minOrderQty
						,Chemistry= '" . $data->chemistry . "'
						,COE='" . $data->coe . "' 
						,Physics='" . $data->physics . "'  
						,Weaving='" . $data->weaving . "'
						,Created_By=$data->createdBy 
						,Modified_By=$data->modifiedBy 
						,Modified_Date='" . $data->modifiedDate . "'
						,Status='A'
						,Expiry_Date_Check='" . $data->expiryDateCheck . "'
						WHERE Item_id=$data->itemId";

        if ($query = $this->db->query($update)) {

            print_r("success");
        } else {
            print_r("failure");
        }
        $result = $query->result_array();

        return $result;
    }

    public function deleteItemMaster($data)
    {
        $delete = "UPDATE item_master SET Status='D' WHERE Item_id=$data->itemId";

        if ($query = $this->db->query($delete)) {

            print_r("success");
        } else {
            print_r("failure");
        }
        $result = $query->result_array();

        return $result;
    }

    public function selectGrnDetails($data)
    {

        $get = "SELECT 
		grn_details.GRN_Id AS grnId,
		grn_details.ERP_GRN_No AS erpGrnNo,
		grn_details.GRN_Date AS grnDate,
		grn_details.Department_Code AS departmentCode,
		grn_details.Supplier_Code AS supplierCode,
		grn_details.Created_By AS createdBy, 
		grn_details.Created_Date AS createdDate, 
		grn_details.Status AS status, 
		suppliermaster.Supplier_Name  AS supplierName,department.Department_Code AS departmentCode
		 FROM grn_details
		 INNER JOIN department ON grn_details.Department_Code=department.Department_Code
		 INNER JOIN suppliermaster ON grn_details.Supplier_Code=suppliermaster.Supplier_Code
		 WHERE grn_details.Status = 'A' AND department.Department_Code=$data->departmentCode;";
        $query = $this->db->query($get);
        $result = $query->result_array();
        return $result;
    }

    public function selectGrnItems($data)
    {
        $get = "SELECT grn_items.Sno AS sNo,
		grn_items.Sno AS sNo,
		grn_items.GRN_Id AS grnId,
		grn_items.Item_id AS itemId,
		grn_items.Item_Code AS itemCode,
		grn_items.Item_Description AS itemDescription,
		grn_items.Batch_No AS batchNo,
		grn_items.Expiry_Date AS expiryDate,
		grn_items.Quantity AS quantity,
		grn_items.Status AS status,
		item_master.Item_Code AS itemCode,item_master.Item_Description AS itemDescription
		FROM grn_items
		INNER JOIN item_master ON grn_items.Item_id=item_master.Item_id
	    WHERE grn_items.Status = 'A' AND grn_items.GRN_Id=$data->grnId";

        $query = $this->db->query($get);
        $result = $query->result_array();

        return $result;
    }



    public function insertGrnDetails($data)
    {

        if (empty($data->grnItems) != 1) {
            $erp = $this->db->query("SELECT ERP_GRN_No AS erpNo FROM erp;")->result_array();
            $erpNo = $erp[0]['erpNo'];

            $updateErp = $this->db->query("UPDATE erp SET ERP_GRN_No= $erpNo+1;");

            $erpUpdated = $this->db->query("SELECT ERP_GRN_No AS erpNo FROM erp;")->result_array();
            $insert = "INSERT INTO grn_details 
			   (ERP_GRN_No,
				GRN_Date,  
				Department_Code,  
				Supplier_Code,  
				Created_By,  
				Status) 
				VALUES(
			'" . $erpUpdated[0]['erpNo'] . "',
		'" . $data->grnDate . "'
			 ,$data->departmentCode 
			 ,$data->supplierCode 
			, $data->createdBy 
		
			,'A'	)";

            $query = $this->db->query($insert);

            $grnIdDesc = "SELECT GRN_Id FROM grn_details ORDER BY GRN_Id DESC LIMIT 1;";
            $selecQuery = $this->db->query($grnIdDesc);
            $selecres = $selecQuery->result_array();

            $grnId = $selecres[0]['GRN_Id'];

            if ($selecres != '') {
                $result = $this->insertGrnItems($data, $grnId);
            }
        } else {
            print_r("Error");
            exit;
        }
        return $result;
    }

    public function insertGrnItems($data, $grnId)
    {
        if ($data->grnItems != '') {
            for ($i = 0; $i < count($data->grnItems); $i++) {
                $itemId = $data->grnItems[$i]->itemId;
                $itemCode = $data->grnItems[$i]->itemCode;
                $itemDescription = $data->grnItems[$i]->description;
                $batchNo = $data->grnItems[$i]->batchNo;
                $expiryDate = $data->grnItems[$i]->expiryDate;
                $quantity = $data->grnItems[$i]->quantity;
                $expiryDateChecked = $data->grnItems[$i]->expiryDateChecked;

                if ($itemId != '' && $itemCode != '') {

                    $grnInsert = "INSERT INTO grn_items(
						GRN_Id,
						Item_id,
						Item_Code,
						Item_Description,
						Batch_No,
						Expiry_Date,
						Quantity,
						Status,
						Expiry_Date_Check)

					VALUES(
							 $grnId,
							 $itemId,
						'" . $itemCode . "',
						'" . $itemDescription . "',
						'" . $batchNo . "',
						'" . $expiryDate . "',
							 $quantity,
							 'A',
						'" . $expiryDateChecked . "');";

                    if ($grnInsert != "") {
                        $query = $this->db->query($grnInsert);
                    } else {
                        print_r('failure');
                    }
                }
            }
        } else {
            print_r("error");
        }

        // to find current stock data by itemId

        $current_Stock = array();
        for ($i = 0; $i < count($data->grnItems); $i++) {
            $itemId = $data->grnItems[$i]->itemId;
            $currentStock = "SELECT Additions AS additions,Item_Id AS itemIdCurrent,Batch_No as batchNo,Expiry_Date AS expiryDate FROM current_stock WHERE Item_Id='" . $itemId . "';";
            array_push($current_Stock,  $currentStock);
        }

        //  to query the current stock
        $currentStockRes = array();
        for ($i = 0; $i < count($current_Stock); $i++) {
            $addition = $this->db->query($current_Stock[$i]);
            array_push($currentStockRes, $addition);
        }

        // to store the result array of the current stock
        $totalRes = array();
        for ($i = 0; $i < count($currentStockRes); $i++) {
            $id = $currentStockRes[$i]->result_array();
            foreach ($id as $key => $value) {
                array_push($totalRes, $value);
            }
        }

        $result = false;

        for ($i = 0; $i < count($data->grnItems); $i++) {
            $insert_No = 1;
            if ($data->grnItems[$i]->expiryDateChecked == 'Y') {
                if ($data->grnItems[$i]->itemId == $totalRes[$i]['itemIdCurrent'] && $data->grnItems[$i]->batchNo == $totalRes[$i]['batchNo'] && $data->grnItems[$i]->expiryDate == $totalRes[$i]['expiryDate']) {
                    $insert_No = 0;
                }
            } else {
                if ($data->grnItems[$i]->itemId == $totalRes[$i]['itemIdCurrent']) {
                    $insert_No = 0;
                }
            }
            if ($insert_No == 0) {
                $updateAdd = "UPDATE current_stock SET Additions='" . $totalRes[$i]['additions'] . "' + '" . $data->grnItems[$i]->quantity . "' WHERE Item_Id='" . $data->grnItems[$i]->itemId . "';";
                $updatequery = $this->db->query($updateAdd);
                if ($updatequery == 1) {
                    $result = true;
                } else {
                    $result = false;
                }
            } else {
                $insert = "INSERT INTO current_stock(Department_Code,
				Item_Code,
				Batch_No,
				Expiry_Date,
				Item_Id,
				Opening,
				Additions,
				Deletions,
				Closing,
				Modified_By,
				Modified_Date,
				Status)
				VALUES(
					'" . $data->departmentCode . "',
					'" . $data->grnItems[$i]->itemCode . "',
					'" . $data->grnItems[$i]->batchNo . "',
					'" . $data->grnItems[$i]->expiryDate . "',
					'" . $data->grnItems[$i]->itemId . "',
					0,
					'" . $data->grnItems[$i]->quantity . "',
					0,
					0,
					'" . $data->modifiedBy . "',
					'" . $data->modifiedDate . "',
					'A') ";

                $insertQuery = $this->db->query($insert);
                if ($insertQuery == 1) {
                    $result = true;
                } else {
                    $result = false;
                }
            }
        }

        return $result;
    }

    public function selectCurrent($data)
    {

        $select = "SELECT current_stock.Stock_Id AS stockId,
		current_stock.Department_Code AS departmentCode, 
		current_stock.Item_Code AS itemCode, 
		current_stock.Batch_No AS batchNo, 
		current_stock.Expiry_Date AS expiryDate, 
		current_stock.Item_Id AS itemId, 
		current_stock.Opening AS opening, 
		current_stock.Additions AS additions, 
		current_stock.Deletions AS deletions, 
		current_stock.Closing AS closing, 
		current_stock.Modified_By AS modifiedBy, 
		current_stock.Modified_Date AS modifiedDate, 
		current_stock.Status AS status,
		item_master.Item_Code AS itemCode,item_master.Item_Description AS itemDescription
		FROM current_stock 
		INNER JOIN item_master ON  item_master.Item_Id=current_stock.Item_Id
		WHERE current_stock.Status='A' AND Department_Code=$data->depCode;";
        $query = $this->db->query($select);
        $result = $query->result_array();
        return $result;
    }


    public function insertConsumption($data)
    {
        $query = $this->db->query("INSERT INTO consumption
		(Department_Code,
		Cons_Date,
		Item_Id,
		Item_Code,
		Batch_No,
		Expiry_Date,
		Quantity,
		
		Status)
		VALUES(
			   $data->departmentCode,
			'" . $data->constDate . "',
		   	   $data->itemId,
			'" . $data->itemCode . "',
			'" . $data->batchNo . "',
			'" . $data->expiryDate . "',
			   $data->quantity,
			  
			   'A') ;");
        $currentStock = "SELECT Deletions AS deletions,Item_Id AS itemId,Batch_No as batchNo,Expiry_Date AS expiryDate FROM current_stock WHERE Item_Id=$data->itemId;";
        $curQuery = $this->db->query($currentStock);
        $result = $curQuery->result_array();
        if ($data->itemId == $result[0]['itemId']) {
            $updatequery = $this->db->query("UPDATE current_stock SET Deletions = '" . $data->quantity . "' + '" . $result[0]['deletions'] . "' WHERE Item_Id='" . $data->itemId . "';");
        }

        return true;
    }
    public function insertTransfers($data)
    {

        $query = $this->db->query("INSERT INTO transfers
		(Item_Id,
		Quantity,
		Item_Code,
		Batch_No,
		Expiry_Date,
		From_Dept_Code,
		To_Dept_Code,
		Created_Date,
		Status)
		VALUES(
			     $data->itemId,
			     $data->quantity,
			'" . $data->itemCode . "',
			'" . $data->batchNo . "',
			'" . $data->expiryDate . "',
			     $data->fromDeptCode,
			     $data->toDeptCode,
			  '" . $data->createdDate . "',
			     'A') ;");

        $currentStockFrom = $this->db->query("SELECT Additions AS additions,
			Deletions AS deletions,
			Item_Id AS itemId,
			Batch_No AS batchNo,
			Department_Code AS depCode,
			Expiry_Date AS expiryDate FROM current_stock WHERE Item_Id=$data->itemId and Department_Code=$data->fromDeptCode")->result_array();
        $currentStockTo = $this->db->query("SELECT Additions AS additions,
			Deletions AS deletions,
			Item_Id AS itemId,
			Batch_No AS batchNo,
			Department_Code AS depCode,
			Expiry_Date AS expiryDate FROM current_stock WHERE Item_Id=$data->itemId and Department_Code=$data->toDeptCode")->result_array();


        if ($data->itemId == $currentStockFrom[0]['itemId']) {
            $updateFromQuery = $this->db->query("UPDATE current_stock SET Deletions = '" . $data->quantity . "' + '" . $currentStockFrom[0]['deletions'] . "' WHERE Item_Id='" . $data->itemId . "' && Department_Code=$data->fromDeptCode ;");

            if (empty($currentStockTo)) {
                $insertStock = $this->db->query("INSERT INTO current_stock(
				Department_Code,
				Item_Code,
				Batch_No,
				Expiry_Date,
				Item_Id,
				Opening,
				Additions,
				Deletions,
				Closing,
				Modified_By,
				Modified_Date,
				Status)
				VALUES(
					'" . $data->toDeptCode . "',
					'" . $data->itemCode . "',
					'" . $data->batchNo . "',
					'" . $data->expiryDate . "',
					'" . $data->itemId . "',
					0,
					'" . $data->quantity . "',
					0,
					0,
					'" . $data->modifiedBy . "',
					'" . $data->modifiedDate . "',
					'A') ");
            } else {
                $updateToQuery = $this->db->query("UPDATE current_stock SET Additions = '" . $data->quantity . "' + '" . $currentStockFrom[0]['additions'] . "' WHERE Item_Id='" . $data->itemId . "' && Department_Code=$data->toDeptCode ;");
            }
        }


        return true;
    }

    public function selectConsumption()
    {
        $select = "SELECT consumption.Cons_Id AS consId,
		consumption.Department_Code AS departmentCode,
		consumption.Cons_Date AS consDate,
		consumption.Item_Id AS itemId,
		consumption.Item_Code AS itemCode,
		consumption.Batch_No AS batchNo,
		consumption.Expiry_Date AS expiryDate,
		consumption.Quantity AS quantity,
		consumption.Created_By AS createdBy,
		consumption.Created_Date AS createdDate,
		consumption.Status AS status,item_master.Item_Description AS itemDescription
	    FROM consumption
		INNER JOIN item_master ON consumption.Item_Id=item_master.Item_Id
		WHERE consumption.Status='A';";
        $query = $this->db->query($select);
        $res = $query->result_array();
        return $res;
    }


    public function selectTransfers()
    {
        $dep = $this->db->query("SELECT Department_Code AS departmentCode,Department_Name AS departmentName FROM department ")->result_array();

        $select = "SELECT transfers.Trans_Id AS transId,
		transfers.Item_Id AS itemId,
		transfers.Quantity AS quantity,
		transfers.Item_Code AS itemCode,
		transfers.Batch_No AS batchNo,
		transfers.Expiry_Date AS expiryDate,
		transfers.From_Dept_Code AS fromDeptCode,
		transfers.To_Dept_Code AS toDeptCode,
		transfers.Created_By AS createdBy,
		transfers.Created_Date AS createdDate,
		transfers.Status AS status,item_master.Item_Description AS itemDescription
		FROM transfers
		INNER JOIN item_master ON item_master.Item_Id=transfers.Item_Id
		WHERE transfers.Status='A' ";
        $query = $this->db->query($select);
        $res = $query->result_array();
        $department = array($dep, $res);
        return $department;
    }

    //pagination

    public function itemDataCount()
    {
        $count = "SELECT COUNT(Item_id) AS itemId FROM item_master WHERE Status='A';";
        $res = $this->db->query($count);
        $result = $res->result_array();
        $total = $result[0]['itemId'];
        $data = ceil($total / 10);

        return $data;
    }

    public function itemPagination($data)
    {

        $noOfPage = $data->pageNo;
        $resultPerPage = 10;
        $finalResult = ($noOfPage - 1) * $resultPerPage;

        $select = "SELECT Item_id AS itemId,
		Item_Code AS itemCode,
		Item_Description AS itemDescription,
		Item_Description1 AS itemDescription1,
		UOM AS unit,
		Min_Order_Qty AS minOrderQty ,
		Chemistry AS chemistry,
		COE AS coe,
		Physics AS physics,
		Weaving AS weaving,
		Created_By AS createdBy,
		Created_Date AS createdDate,
		Modified_By AS modifiedBy,
		Modified_Date AS modifiedDate,
		Status AS status,
		Expiry_Date_Check AS expiryDateCheck
	    FROM item_master
	    WHERE Status='A' ORDER BY itemId  DESC LIMIT $finalResult ,$resultPerPage";

        $query = $this->db->query($select);
        $res = $query->result_array();
        return $res;
    }
    public function grnDataCount($data)
    {
        $count = "SELECT COUNT(GRN_Id) AS grnId FROM grn_details WHERE Status='A' AND Department_Code=$data->depCode;";
        $res = $this->db->query($count);
        $result = $res->result_array();
        $total = $result[0]['grnId'];
        $data = ceil($total / 10);

        return $data;
    }
    public function grnPagination($data)
    {
        $noOfPage = $data->pageNo;
        $resultPerPage = 10;
        $finalResult = ($noOfPage - 1) * $resultPerPage;
        $get = "SELECT 
		grn_details.GRN_Id AS grnId,
		grn_details.ERP_GRN_No AS erpGrnNo,
		grn_details.GRN_Date AS grnDate,
		grn_details.Department_Code AS departmentCode,
		grn_details.Supplier_Code AS supplierCode,
		grn_details.Created_By AS createdBy, 
		grn_details.Created_Date AS createdDate, 
		grn_details.Status AS status, 
		suppliermaster.Supplier_Name  AS supplierName,department.Department_Code AS departmentCode
		 FROM grn_details
		 INNER JOIN department ON grn_details.Department_Code=department.Department_Code
		 INNER JOIN suppliermaster ON grn_details.Supplier_Code=suppliermaster.Supplier_Code
		 WHERE grn_details.Status = 'A' AND department.Department_Code=$data->departmentCode ORDER BY grnId DESC LIMIT $finalResult ,$resultPerPage;";

        $query = $this->db->query($get);
        $result = $query->result_array();

        return $result;
    }
    public function consumptionDataCount($data)
    {
        $count = "SELECT COUNT(Cons_Id) AS consId FROM consumption WHERE Status='A' AND Department_Code=$data->depCode";
        $res = $this->db->query($count);
        $result = $res->result_array();
        $total = $result[0]['consId'];
        $data = ceil($total / 10);

        return $data;
    }

    public function consumptionPagination($data)
    {
        $noOfPage = $data->pageNo;
        $resultPerPage = 10;
        $finalResult = ($noOfPage - 1) * $resultPerPage;

        $select = "SELECT consumption.Cons_Id AS consId,
		consumption.Department_Code AS departmentCode,
		consumption.Cons_Date AS consDate,
		consumption.Item_Id AS itemId,
		consumption.Item_Code AS itemCode,
		consumption.Batch_No AS batchNo,
		consumption.Expiry_Date AS expiryDate,
		consumption.Quantity AS quantity,
		consumption.Created_By AS createdBy,
		consumption.Created_Date AS createdDate,
		consumption.Status AS status,item_master.Item_Code AS itemCode,item_master.Item_Description
	    FROM consumption
		INNER JOIN item_master ON consumption.Item_Id=item_master.Item_Id
		INNER JOIN department ON consumption.Department_Code=department.Department_Code
		WHERE consumption.Status='A' AND department.Department_Code=$data->departmentCode ORDER BY consId  DESC LIMIT $finalResult ,$resultPerPage;";

        $query = $this->db->query($select);
        $res = $query->result_array();
        return $res;
    }
    public function transferDataCount($data)
    {

        $count = "SELECT COUNT(Trans_Id) AS transId FROM transfers WHERE Status='A' AND From_Dept_Code=$data->depCode";
        $res = $this->db->query($count);
        $result = $res->result_array();
        $total = $result[0]['transId'];
        $data = ceil($total / 10);
        return $data;
    }

    public function transferPagination($data)
    {
        $noOfPage = $data->pageNo;
        $resultPerPage = 10;
        $finalResult = ($noOfPage - 1) * $resultPerPage;
        $dep = $this->db->query("SELECT Department_Code AS departmentCode,Department_Name AS departmentName FROM department ")->result_array();

        $select = "SELECT transfers.Trans_Id AS transId,
		transfers.Item_Id AS itemId,
		transfers.Quantity AS quantity,
		transfers.Item_Code AS itemCode,
		transfers.Batch_No AS batchNo,
		transfers.Expiry_Date AS expiryDate,
		transfers.From_Dept_Code AS fromDeptCode,
		transfers.To_Dept_Code AS toDeptCode,
		transfers.Created_By AS createdBy,
		transfers.Created_Date AS createdDate,
		transfers.Status AS status,item_master.Item_Description AS itemDescription
		FROM transfers
		INNER JOIN item_master ON item_master.Item_Id=transfers.Item_Id
		WHERE transfers.Status='A' AND transfers.From_Dept_Code=$data->depCode ORDER BY transId  DESC LIMIT $finalResult ,$resultPerPage;";

        $query = $this->db->query($select);
        $res = $query->result_array();

        $total = array('dep' => $dep, 'data' => $res);
        return $total;
    }

    public function searchItem($data)
    {
        $search = $this->db->query("SELECT Item_id AS itemId,
		Item_Code AS itemCode,
		Item_Description AS itemDescription,
		Item_Description1 AS itemDescription1,
		UOM AS unit,
		Min_Order_Qty AS minOrderQty ,
		Chemistry AS chemistry,
		COE AS coe,
		Physics AS physics,
		Weaving AS weaving,
		Created_By AS createdBy,
		Created_Date AS createdDate,
		Modified_By AS modifiedBy,
		Modified_Date AS modifiedDate,
		Status AS status,
		Expiry_Date_Check AS expiryDateCheck FROM $data->table 
		WHERE Item_Description LIKE '%{$data->query}%'")->result_array();

        return $search;
    }

    public function searchStock($data)
    {
        $res = array();
        $op = array();
        $depCode = $this->db->query("SELECT Department_Code FROM $data->table WHERE Status='A';")->result_array();
        for ($i = 0; $i < count($depCode); $i++) {
            $d = $this->db->query("SELECT Stock_Id AS stockId,
			current_stock.Department_Code AS depCode,
			current_stock.Item_Code AS itemCode,
			Batch_No AS batchNo,
			Expiry_Date AS expDate,
			current_stock.Item_Id AS itemId,
			Opening AS opening,
			Additions AS additions,
			Deletions AS deletions,
			Closing AS closing,
			department.Department_Name AS depName,
			item_master.Item_Description AS itemDesc
			FROM $data->table
			INNER JOIN item_master ON current_stock.Item_Code = item_master.Item_Code
			INNER JOIN department ON current_stock.Department_Code=department.Department_Code
			WHERE item_master.Item_Description LIKE '%{$data->query}%' AND department.Department_Code = '" . $depCode[$i]['Department_Code'] . "' ")->result_array();
            array_push($res, $d);
        }


        for ($i = 0; $i < count($res); $i++) {
            if ($res[$i][0] != null) {
                array_push($op, $res[$i][0]);
            }
        }


        return $op;
    }
    public function selectStock()
    {
        $selectStock = $this->db->query(
            "SELECT Stock_Id AS stockId,
			current_stock.Department_Code AS depCode,
			current_stock.Item_Code AS itemCode,
			Batch_No AS batchNo,
			Expiry_Date AS expDate,
			current_stock.Item_Id AS itemId,
			Opening AS opening,
			Additions AS additions,
			Deletions AS deletions,
			Closing AS closing,
			department.Department_Name AS depName,
			item_master.Item_Description AS itemDesc
			FROM current_stock
			INNER JOIN department ON current_stock.Department_Code=department.Department_Code
			INNER JOIN item_master ON current_stock.Item_Id = item_master.Item_Id
			WHERE current_stock.Status ='A';"
        )->result_array();

        return $selectStock;
    }
}
