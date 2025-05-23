<?php

namespace App\Http\Controllers\Organizations\Purchase;

use App\Models\PurchaseOrdersModel;
use Illuminate\Http\Request;
use App\Http\Services\Organizations\Purchase\PurchaseOrderServices;
use App\Http\Controllers\Controller;
use Validator;
use App\Models\{
    BusinessApplicationProcesses
};
use App\Http\Controllers\Organizations\CommanController;


class PurchaseOrderController extends Controller
{
    public function __construct()
    {
        $this->service = new PurchaseOrderServices();
        $this->serviceCommon = new CommanController();

    }

    public function index($requistition_id)
    {
        $getOutput = PurchaseOrdersModel::where('requisition_id', base64_decode($requistition_id))->get();

        return view(
            'organizations.purchase.addpurchasedetails.list-purchase-orders',
            compact(
                'getOutput',
                'requistition_id'
            )
        );
    }

    public function create(Request $request)
    {
        $requistition_id = $request->requistition_id;
        $title = 'create invoice';
        return view(
            'organizations.purchase.addpurchasedetails.add-purchase-orders',
            compact(
                'title',
                'requistition_id'
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        $rules = [
            'client_name' => 'required',
            'phone_number' => 'required',
            'email' => 'required',
            'tax' => 'required',
            'invoice_date' => 'required',
            'gst_number' => 'required',
            'payment_terms' => 'required',
            'client_address' => 'required',
            'discount' => 'required',
            'status' => 'required',
            'note' => 'nullable',
        ];

        $messages = [
            'client_name.required' => 'The Client Name is required.',
            'phone_number.required' => 'The Phone Number is required.',
            'email.required' => 'The Email is required.',
            'tax.required' => 'The Tax is required.',
            'invoice_date.required' => 'The Invoice Date is required.',
            'gst_number.required' => 'The GST Number is required.',
            'payment_terms.required' => 'The Payment Terms is required.',
            'client_address.required' => 'The Client Address is required.',
            'discount.required' => 'The Discount is required.',
            'status.required' => 'The Status is required.',
            'note.required' => 'The Note is required.',
        ];

        try {
            $validation = Validator::make($request->all(), $rules, $messages);

            if ($validation->fails()) {
                return redirect('purchase/add-purchase-order')
                    ->withInput()
                    ->withErrors($validation);
            } else {
                $requi_id = $request->requistition_id;
                $add_record = $this->service->submitBOMToOwner($request);
                if ($add_record) {
                    $msg = $add_record['msg'];
                    $status = $add_record['status'];
                    if ($status == 'success') {
                        return redirect('purchase/list-purchase-order/' . $requi_id)->with(compact('msg', 'status'));
                    } else {
                        return redirect('purchase/add-purchase-order')->withInput()->with(compact('msg', 'status'));
                    }
                }
            }
        } catch (Exception $e) {
            return redirect('purchase/add-business')->withInput()->with(['msg' => $e->getMessage(), 'status' => 'error']);
        }
    }

    public function store_old(Request $request)
    {
        $rules = [
            'client_name' => 'required',
            'phone_number' => 'required',
            'email' => 'required',
            'tax' => 'required',
            'invoice_date' => 'required',
            'gst_number' => 'required',
            'payment_terms' => 'required',
            'client_address' => 'required',
            'discount' => 'required',
            'status' => 'required',
            'note' => 'nullable',
        ];


        $amount = 0;
        foreach ($request->items as $item) {
            $amount += $item['amount'];
        }

        $itemsJson = json_encode($request->items);


        $invoice = new PurchaseOrdersModel([
            'client_name' => $request->client_name,
            'phone_number' => $request->phone_number,
            'tax' => $request->tax,
            'email' => $request->email,
            'client_address' => $request->client_address,
            'gst_number' => $request->gst_number,
            'invoice_date' => $request->invoice_date,
            'payment_terms' => $request->payment_terms,
            'items' => $itemsJson,
            'discount' => $request->discount,
            'total' => $amount,
            'note' => $request->note,
            'status' => $request->status,
        ]);

        if ($invoice->save()) {
            $msg = 'Invoice has been created';
            $status = 'success';

            return redirect('purchase/list-purchase-order')->with(compact('msg', 'status'));
        } else {
            $msg = 'Failed to create invoice';
            $status = 'error';

            return redirect('purchase/add-purchase-order')->withInput()->with(compact('msg', 'status'));
        }
    }



    public function show(Request $request)
    {
        $show_data_id = base64_decode($request->id);
        $invoice = PurchaseOrdersModel::find($show_data_id);
        $title = 'view invoice';
        return view('organizations.purchase.addpurchasedetails.show-purchase-orders', compact('invoice', 'title'));
    }

    public function show21(Request $request)
    {
        $show_data_id = base64_decode($request->id);
        $invoice = PurchaseOrdersModel::find($show_data_id);
        $title = 'view invoice';
        return view('organizations.purchase.addpurchasedetails.show-purchase-orders21', compact('invoice', 'title'));
    }

    public function showpurchase(Request $request)
    {
        $show_data_id = base64_decode($request->id);
        $invoice = PurchaseOrdersModel::find($show_data_id);
        $title = 'view invoice';
        return view('organizations.purchase.addpurchasedetails.show-purchase-orders1', compact('invoice', 'title'));
    }


    public function edit(Request $request)
    {
        $show_data_id = base64_decode($request->id);
        $invoice = PurchaseOrdersModel::find($show_data_id);
        $title = 'edit invoice';
        return view(
            'organizations.purchase.addpurchasedetails.edit-purchase-orders',
            compact(
                'title',
                'invoice'
            )
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invoice $invoice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'client_name' => 'required',
            'phone_number' => 'required',
            'email' => 'required',
            'tax' => 'required',
            'client_address' => 'required',
            'gst_number' => 'required',
            'invoice_date' => 'required',
            'items' => 'required',
            'note' => 'nullable',
        ]);


        $itemsJson = json_encode($request->items);


        $amount = 0;
        foreach ($request->items as $item) {
            $amount += $item['amount'];
        }

        $invoice = PurchaseOrdersModel::find($request->id);
        $invoice->update([
            'client_name' => $request->client_name,
            'phone_number' => $request->phone_number,
            'tax' => $request->tax,
            'email' => $request->email,
            'client_address' => $request->client_address,
            'gst_number' => $request->gst_number,
            'invoice_date' => $request->invoice_date,
            'payment_terms' => $request->payment_terms,
            'items' => $itemsJson,
            'discount' => $request->discount,
            'total' => $amount,
            'note' => $request->note,
            'status' => $request->status,
        ]);
        if ($invoice->wasChanged()) {
            $msg = 'Invoice has been updated';
            $status = 'success';
            return redirect('purchase/list-purchase-order')->with(compact('msg', 'status'));
        } else {
            $msg = 'No changes were made to the invoice';
            $status = 'error';
            return redirect('purchase/list-purchase-order')->with(compact('msg', 'status'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        Invoice::findOrFail($request->id)->delete();
        $notification = notify('Invoice has been deleted successfully');
        return back()->with($notification);
    }


    public function submitPurchaseOrderToOwnerForReview(Request $request)
    {
        try {
            $requistition_id = base64_decode($request->requistition_id);
            
            $data_purchase_orders_id = PurchaseOrdersModel::where('requisition_id', $requistition_id)->pluck('purchase_orders_id');
            
            $data_purchase_orders_update = PurchaseOrdersModel::where('requisition_id', $requistition_id)->first();
            $data_purchase_orders_update->purchase_status_from_purchase = config('constants.PUCHASE_DEPARTMENT.PO_NEW_SENT_TO_HIGHER_AUTH_FOR_APPROVAL');
            $data_purchase_orders_update->save();

            $business_application = BusinessApplicationProcesses::where('requisition_id', $requistition_id)->first();

            if ($business_application) {
                $business_application->business_status_id = config('constants.HIGHER_AUTHORITY.LIST_PO_TO_BE_APPROVE_FROM_PURCHASE');
                $business_application->purchase_order_id = $data_purchase_orders_id;
                $business_application->purchase_order_submited_to_owner_date = date('Y-m-d');

                // $business_application->grn_no = '0';
                // $business_application->store_receipt_no = '0';
                $business_application->save();

            }

            $msg = 'Purchase order submitted successfully';
            $status = 'success';
            return redirect('purchase/list-purchase')->with(compact('msg', 'status'));
        } catch (\Exception $e) {
            return [
                'msg' => $e->getMessage(),
                'status' => 'error'
            ];
        }
    }


    public function checkDetailsBeforeSendPOToVendor($purchase_order_id)
    {
        try {
            $getOrganizationData = $this->serviceCommon->getAllOrganizationData();

            $data = $this->serviceCommon->getPurchaseOrderDetails($purchase_order_id);
            $purchaseOrder = $data['purchaseOrder'];
            $purchaseOrderDetails = $data['purchaseOrderDetails'];

            return view(
                'organizations.purchase.purchase.purchase-order-details',
                compact('purchase_order_id', 'purchaseOrder', 'purchaseOrderDetails', 'getOrganizationData')
            );


            // return view('organizations.business.purchase-order.purchase-order-details', compact('purchase_order_id', 'purchaseOrder', 'purchaseOrderDetails', 'getOrganizationData'));


        } catch (Exception $e) {
            return ['status' => 'error', 'msg' => $e->getMessage()];
        }
    }



    public function listAllApprovedPOToBeChecked($purchase_order_id)
    {
        try {
            $delete = $this->service->listAllApprovedPOToBeChecked($purchase_order_id);
            if ($delete) {
                $status = 'success';
                $msg = 'Purchase order mail sent to vendor.';
            } else {
                $status = 'success';
                $msg = 'Purchase order mail sent to vendor.';
            }

            return redirect('purchase/list-purchase-order-approved-sent-to-vendor')->with(compact('msg', 'status'));

        } catch (Exception $e) {
            return ['status' => 'error', 'msg' => $e->getMessage()];
        }
    }


    public function getAllListPurchaseOrderTowardsOwnerDetails($purchase_order_id)
    {
        try {
            $getOrganizationData = $this->serviceCommon->getAllOrganizationData();
            $data = $this->serviceCommon->getPurchaseOrderDetails($purchase_order_id);
            $purchaseOrder = $data['purchaseOrder'];
            $purchaseOrderDetails = $data['purchaseOrderDetails'];

            return view('organizations.purchase.addpurchasedetails.view-purchase-orders-details', compact('purchase_order_id', 'purchaseOrder', 'purchaseOrderDetails', 'getOrganizationData'));
        } catch (Exception $e) {
            return ['status' => 'error', 'msg' => $e->getMessage()];
        }
    }

    public function submitAndSentEmailToTheVendorFinalPurchaseOrder($purchase_order_id)
    {
        try {
            $delete = $this->service->submitAndSentEmailToTheVendorFinalPurchaseOrder($purchase_order_id);
            if ($delete) {
                $status = 'success';
                $msg = 'Purchase order mail sent to vendor.';
            } else {
                $status = 'success';
                $msg = 'Purchase order mail sent to vendor.';
            }

            return redirect('purchase/list-purchase-order-approved-sent-to-vendor')->with(compact('msg', 'status'));

        } catch (Exception $e) {
            return ['status' => 'error', 'msg' => $e->getMessage()];
        }
    }



   

}