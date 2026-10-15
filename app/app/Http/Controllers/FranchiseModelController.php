<?php
namespace App\Http\Controllers;
use App\Models\FranchiseModel;
use Illuminate\Http\Request;
class FranchiseModelController extends Controller {
    public function index() { return view('admin.franchise-models', ['models'=>FranchiseModel::orderBy('sort_order')->get()]); }
    public function edit(?FranchiseModel $model=null) { return view('admin.franchise-model-edit',['model'=>$model ?? new FranchiseModel]); }
    public function save(Request $request, ?FranchiseModel $model=null) {
        $data=$request->validate(['name'=>'required|string|max:255','space'=>'required|string|max:255','investment'=>'required|string|max:255','summary'=>'required|string|max:1000','products'=>'required|string|max:3000','costs'=>'nullable|string|max:5000','notes'=>'nullable|string|max:3000','sort_order'=>'required|integer|min:0|max:10000','published'=>'required|boolean']);
        ($model ?? new FranchiseModel)->fill($data)->save();
        return redirect('/admin/franchise-models')->with('message','Franchise model saved.');
    }
}
