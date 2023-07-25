<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\CategoryLogic;
use App\Http\Controllers\Controller;
use App\Models\allbanners;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function get_banners(){
        allbanners::where('status' , 'Published')->where('bannertype' , 'mobileapp')->orderby('order'  ,'ASC')->get();
        try {
            $banners = allbanners::select(
                "id",
                "banner")
                ->where('status' , 'Published')
                ->where('bannertype' , 'mobileapp')
                ->orderby('order'  ,'ASC')
                ->get();
                $data['all_banners'] = banners_format($banners);
                $data['status'] = true;

                return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }
}
