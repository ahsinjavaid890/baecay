<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Api\V1'], function () {

    Route::get('getcountries', 'ConfigController@getcountries');
    Route::get('getstates/{id}', 'ConfigController@getstates');
    Route::post('addtocart', 'OrderController@addtocart');
    Route::get('addtocartlist/{id}', 'OrderController@addtocartlist');
    Route::POST('removefromcart', 'OrderController@removefromcart');
    Route::post('updatecart', 'OrderController@updatecart');
    Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function () {
        Route::post('userregister', 'VendorAuthController@userregister');
        Route::post('login', 'VendorAuthController@login');
        Route::get('google', 'VendorAuthController@redirectToGoogle');
        Route::get('googlecallback', 'VendorAuthController@handleGoogleCallback');

        Route::middleware(['auth:api'])->get('/logout', function (Request $request) {
            $request->user()->token()->revoke();
        });

        Route::post('register', 'VendorAuthController@register');

        


        // Route::group(['prefix' => 'auth',  'middleware' => ['auth:api']], function () {
        //    Route::get('logout', 'VendorAuthController@logout');
        // });
        
        
 

        // Route::post('check-email', 'CustomerAuthController@check_email');
        // Route::post('verify-email', 'CustomerAuthController@verify_email');

        Route::post('forgot-password', 'PasswordResetController@reset_password_request');
        Route::post('verify-token', 'PasswordResetController@verify_token');
        Route::post('reset-password', 'PasswordResetController@reset_password_submit');

        Route::post('passwordupdate', 'PasswordResetController@passwordupdate');
        // Route::group(['prefix' => 'delivery-man'], function () {
        //     Route::post('login', 'DeliveryManLoginController@login');
        // });

        
    });


    Route::group(['prefix' => 'products_check',  'middleware' => ['auth:api']], function () {

        Route::get('check', 'BakeryController@near_by_bakeries');
        Route::post('login', 'CustomerAuthController@login');
        Route::post('send-otp', 'CustomerAuthController@send_otp');

        Route::post('verify-otp', 'CustomerAuthController@verify_otp');



    });


   
    Route::group(['prefix' => 'delivery-man'], function () {
        Route::get('profile', 'DeliverymanController@get_profile');
        Route::get('current-orders', 'DeliverymanController@get_current_orders');
        Route::get('all-orders', 'DeliverymanController@get_all_orders');
        Route::post('record-location-data', 'DeliverymanController@record_location_data');
        Route::get('order-delivery-history', 'DeliverymanController@get_order_history');
        Route::put('update-order-status', 'DeliverymanController@update_order_status');
        Route::put('update-payment-status', 'DeliverymanController@order_payment_status_update');
        Route::get('order-details', 'DeliverymanController@get_order_details');
        Route::get('last-location', 'DeliverymanController@get_last_location');
        Route::put('update-fcm-token', 'DeliverymanController@update_fcm_token');

        Route::group(['prefix' => 'reviews', 'middleware' => ['auth:api']], function () {
            Route::get('/{delivery_man_id}', 'DeliveryManReviewController@get_reviews');
            Route::get('rating/{delivery_man_id}', 'DeliveryManReviewController@get_rating');
            Route::post('/submit', 'DeliveryManReviewController@submit_review');
        });
    });

    Route::group(['prefix' => 'config'], function () {
        Route::get('/', 'ConfigController@configuration');
    });

    Route::group(['prefix' => 'products'], function () {

        Route::get('get_latest_products', 'ProductController@get_latest_products');
        Route::get('get_best_seller_products', 'ProductController@get_best_seller_products');
        Route::get('get_popular_products', 'ProductController@get_popular_products');
        Route::get('get_featured_products', 'ProductController@get_featured_products');
        Route::get('search_homepage/{id}/{user_id}', 'ProductController@search_homepage');
        Route::get('search_store/{id}/{store_id}/{user_id}', 'ProductController@search_store');
        Route::get('latest', 'ProductController@get_latest_products');
        Route::get('list', 'ProductController@get_stock_products');
        Route::get('attributes/{id}', 'ProductController@get_attributes')->name('get_attributes');
        Route::get('update_status', 'ProductController@update_status')->name('update_status');
        Route::get('delete', 'ProductController@delete_product')->name('delete_product');
        Route::get('coupon/delete', 'ProductController@delete_coupon')->name('delete_coupon');
        Route::get('attribute/delete', 'ProductController@delete_attribute')->name('delete_attribute');
        Route::get('stock/update', 'ProductController@stock_update')->name('stock_update');
        Route::get('set-menu', 'ProductController@get_set_menus');
        Route::get('search', 'ProductController@get_searched_products');
        Route::get('details/{id}/{user_id}', 'ProductController@get_product');
        Route::get('related-products/{product_id}', 'ProductController@get_related_products');
        Route::get('reviews/{product_id}', 'ProductController@get_product_reviews');
        Route::get('rating/{product_id}', 'ProductController@get_product_rating');
        Route::post('reviews/submit', 'ProductController@submit_product_review')->middleware('auth:api');
        Route::get('checksku/{id}', 'ProductController@checksku');

    });

    Route::group(['prefix' => 'stock'], function () {
        Route::get('searchstock/{id}', 'StockController@searchstock');
    });


    Route::group(['prefix' => 'banners'], function () {
        Route::get('/', 'BannerController@get_banners');
    });

    Route::group(['prefix' => 'notifications'], function () {
        Route::get('/', 'NotificationController@get_notifications');
    });

    Route::group(['prefix' => 'categories'], function () {
        
        Route::get('/', 'CategoryController@get_categories');
        Route::get('childes/{category_id}', 'CategoryController@get_childes')->name('child_cat_get');
        Route::get('sub_childes/{category_id}', 'CategoryController@get_sub_childes')->name('sub_child_cat_get');
        Route::get('products/parent/{category_id}/{user_id}', 'CategoryController@get_products_parent');
        Route::get('products/child/{category_id}/{user_id}', 'CategoryController@get_products_child');
        Route::get('products/subchild/{category_id}/{user_id}', 'CategoryController@get_products_sub_child');
    });




    Route::group(['prefix' => 'customer', 'middleware' => 'authapi:api'], function () {
        Route::get('info', 'CustomerController@info');
        Route::POST('update-profile', 'CustomerController@update_profile');
        Route::put('cm-firebase-token', 'CustomerController@update_cm_firebase_token');
        Route::get('transaction-history', 'CustomerController@get_transaction_history');

        Route::group(['prefix' => 'address'], function () {
            Route::get('list/{id}', 'CustomerController@address_list');
            Route::post('add', 'CustomerController@add_new_address');
            Route::post('update', 'CustomerController@update_address');
            Route::get('delete/{id}', 'CustomerController@delete_address');
        });

        Route::group(['prefix' => 'order'], function () {
            Route::get('list/{id}', 'OrderController@get_order_list');
            Route::get('details/{id}', 'OrderController@get_order_details');
            Route::post('place', 'OrderController@place_order');
            Route::put('cancel', 'OrderController@cancel_order');
            Route::get('track', 'OrderController@track_order');
            Route::post('stripepayement', 'OrderController@stripepayement');
        });

        // Chatting
        Route::group(['prefix' => 'message'], function () {
            Route::get('get', 'ConversationController@messages');
            Route::post('send', 'ConversationController@messages_store');
            Route::post('chat-image', 'ConversationController@chat_image');
        });

        Route::group(['prefix' => 'wish-list'], function () {
            Route::get('/{id}', 'WishlistController@wish_list');
            Route::post('add', 'WishlistController@add_to_wishlist');
            Route::post('remove', 'WishlistController@remove_from_wishlist');
        });
    });

    Route::group(['prefix' => 'banners'], function () {
        Route::get('/', 'BannerController@get_banners');
    });

    Route::group(['prefix' => 'coupon', 'middleware' => 'auth:api'], function () {
        Route::get('list', 'CouponController@list');
        Route::get('apply', 'CouponController@apply');
    });


    Route::group(['prefix' => 'brands'], function () {
        Route::get('all', 'BrandController@get_all_brands');
        Route::get('brand/{id}', 'BrandController@get_brand_by_id');
        Route::get('productbybrand/{id}', 'BrandController@get_product_by_brand');
    }); 
    Route::group(['prefix' => 'store'], function () {
        Route::get('detail/{id}/{user_id}', 'StoreController@get_detail');
        Route::get('brand/{id}', 'BrandController@get_brand_by_id');
        Route::get('productbybrand/{id}', 'BrandController@get_product_by_brand');

        Route::get('all', 'StoreController@allstores');

    }); 
});
