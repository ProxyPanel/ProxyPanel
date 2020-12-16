<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Goods;
use App\Models\Article;
use Session;
use Illuminate\Support\Str;

use Jenssegers\Agent\Agent;

class FrontPageController extends Controller
{
    public function home()
    {
        Session::put('register_token', Str::random(16));

       $view['packageList'] = Goods::query()->where('status', 1)->where('is_del', 0)->where('type', 1)->orderBy('sort', 'desc')->limit(4)->get();
       
       $view['articleList'] = Article::query()->where('type', 3)->where('is_del', 0)->orderBy('sort', 'desc')->orderBy('id', 'desc')->limit(4)->get();

        $agent = new Agent();

        if ($agent->isMobile() || $agent->isTablet() ) {
            return view('static-pages.mobile.home', $view);
        } else {
            return view('static-pages.desktop.home', $view);
        }
    }

    public function feature()
    {
        $agent = new Agent();

        if ($agent->isMobile() || $agent->isTablet()) {
            return view('static-pages.mobile.features');
        } else {
            return view('static-pages.desktop.features');
        }
    }

    public function price()
    {

        $view['packageList'] = Goods::query()->where('status', 1)->where('is_del', 0)->where('type', 1)->orderBy('sort', 'desc')->limit(4)->get();

        $agent = new Agent();

        if ($agent->isMobile()) {
            return view('static-pages.mobile.price', $view);
        } else {
            return view('static-pages.desktop.price', $view);
        }
    }

    public function help()
    {

        $view['articleList'] = Article::query()->where('type', 1)->where('is_del', 0)->orderBy('sort', 'desc')->orderBy('id', 'desc')->limit(5)->paginate(5);

        $agent = new Agent();

        if ($agent->isMobile() || $agent->isTablet()) {
            return view('static-pages.mobile.help', $view);
        } else {
            return view('static-pages.desktop.help', $view);
        }
    }

    /* Showed help subpage content */
    public function helpsubpage(Request $request) {

        $id = $request->get('id');

        $view['info'] = Article::query()->where('is_del', 0)->where('id', $id)->first();

        $agent = new Agent();

        if ($agent->isMobile() || $agent->isTablet()) {
            return view('static-pages.mobile.subpage',$view);
        } else {
            return view('static-pages.desktop.help-inner',$view);
        }

    }
    
    
     public function tutorialSubpage(Request $request) {

        $id = $request->get('id');

        $view['info'] = Article::query()->where('is_del', 0)->where('id', $id)->first();

        $agent = new Agent();

        if ($agent->isMobile() || $agent->isTablet()) {
            return view('static-pages.mobile.subpage',$view);
        } else {
            return view('static-pages.desktop.help-inner',$view);
        }

    }
    

    public function account()
    {
        $agent = new Agent();

        if ($agent->isMobile() || $agent->isTablet()) {
            return view('static-pages.mobile.account');
        } else {
            return view('static-pages.desktop.home');
        }
    }

    public function vpn()
    {
        $agent = new Agent();

        if ($agent->isMobile() || $agent->isTablet()) {
            return view('static-pages.mobile.vpn-apps');
        } else {
            return view('static-pages.desktop.vpn-apps');
        }
    }


    public function contact() {
        $agent = new Agent();

        if ($agent->isMobile() || $agent->isTablet()) {
            return view('static-pages.mobile.contact');
        } else {
            return view('static-pages.desktop.contact');
        }
    }
    
     public function tutorial() {
        $agent = new Agent();

        if ($agent->isMobile() || $agent->isTablet()) {
            return view('static-pages.mobile.tutorial');
        } else {
            return view('static-pages.desktop.tutorial');
        }
    }
    
     public function term() {
        $agent = new Agent();

        if ($agent->isMobile() || $agent->isTablet()) {
            return view('static-pages.mobile.term');
        } else {
            return view('static-pages.desktop.term');
        }
    }
}
