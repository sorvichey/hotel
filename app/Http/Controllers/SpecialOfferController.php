<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Session;
use Auth;
use Intervention\Image\ImageManagerStatic as Image;
class SpecialOfferController extends Controller
{
    public function __construct()
    {
       $this->middleware('auth');
    }
    // index
    public function index()
    {
        $data['special_offers'] = DB::table('special_offers')
            ->orderBy('id', 'desc')
            ->where('active', 1)
            ->limit(1)
            ->get();
        return view('special-offers.index', $data);
    }
    public function create()
    {
        return view('special-offers.create');
    }
    public function save(Request $r)
    {
        $data = array(
            'title' => $r->title,
            'description' => $r->description,
            'url' => $r->url,
        );
        $i = DB::table('special_offers')->insertGetId($data);
        if($r->hasFile('featured_image')) {
            $file = $r->file('featured_image');
            $file_name = $file->getClientOriginalName();
            $ss = substr($file_name, strripos($file_name, '.'), strlen($file_name));
            $file_name = 'offer' .$i . $ss;
            $destinationPath = 'uploads/special-offers/'; // usually in public folder

            $file->move($destinationPath, $file_name);
            $data['featured_image'] = $file_name;
            $i = DB::table('special_offers')->where('id', $i)->update($data);
        }
        if ($i) {
            $r->session()->flash("sms", "New special offer has been created successfully!");
            return redirect("/admin/special-offer/create");
        } else {
            $r->session()->flash("sms1", "Fail to create new special offer!");
            return redirect("/admin/special-offer/create")->withInput();
        }   
    }
    // delete
    public function delete($id)
    {
        DB::table('special_offers')->where('id', $id)->update(["active"=>0]);
        return redirect('/admin/special-offer');
    }
    public function edit($id)
    {
        $data['special_offer'] = DB::table('special_offers')
            ->where('id',$id)->first();
        return view('special-offers.edit', $data);
    }
    
    public function update(Request $r)
    {
        $data = array(
            'title' => $r->title,
            'url' => $r->url,
            'description' => $r->description
        );
        if ($r->featured_image) {
            $file = $r->file('featured_image');
            $file_name = $file->getClientOriginalName();
            $ss = substr($file_name, strripos($file_name, '.'), strlen($file_name));
            $file_name = 'offer' .$r->id . $ss;
            $destinationPath = 'uploads/special-offers/'; // usually in public folder
         
            $file->move($destinationPath, $file_name);
            $data['featured_image'] = $file_name;        
        }
        $sms = "All changes have been saved successfully.";
        $sms1 = "Fail to to save changes, please check again!";
        $i = DB::table('special_offers')->where('id', $r->id)->update($data);
        if ($i)
        {
            $r->session()->flash('sms', $sms);
            return redirect('/admin/special-offer/edit/'.$r->id);
        }
        else
        {
            $r->session()->flash('sms1', $sms1);
            return redirect('/admin/special-offer/edit/'.$r->id);
        }
    }
}

