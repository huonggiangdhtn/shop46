<nav class="side-nav">
   
  <ul>
      <li>
        <a href="{{route('admin')}}" class="side-menu side-menu{{$active_menu=='dashboard'?'--active':''}}">
            <div class="side-menu__icon"> <i data-lucide="home"></i> </div>
            <div class="side-menu__title"> Dashboard </div>
        </a>
      </li> 
      <!-- setting menu -->
      <li>
          <a href="javascript:;.html" class="side-menu side-menu{{($active_menu=='setting_list'|| $active_menu=='log_list'||$active_menu=='banner_add'|| $active_menu=='banner_list')?'--active':''}}">
              <div class="side-menu__icon"> <i data-lucide="settings"></i> </div>
              <div class="side-menu__title">
                  Cài đặt
                  <div class="side-menu__sub-icon transform"> <i data-lucide="chevron-down"></i> </div>
              </div>
          </a>
          <ul class="{{($active_menu=='setting_list'|| $active_menu=='banner_add'|| $active_menu=='banner_list')?'side-menu__sub-open':''}}">
              <li>
                  <a href="{{route('banner.index')}}" class="side-menu {{$active_menu=='banner_list'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="image"></i> </div>
                      <div class="side-menu__title">Danh sách banner </div>
                  </a>
              </li>
              <li>
                  <a href="{{route('banner.create')}}" class="side-menu {{$active_menu=='banner_add'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="plus"></i> </div>
                      <div class="side-menu__title"> Thêm banner</div>
                  </a>
              </li>
              <li>
                  <a href="{{route('setting.edit',1)}}" class="side-menu {{$active_menu=='setting_list'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="key"></i> </div>
                      <div class="side-menu__title"> Thông tin công ty</div>
                  </a>
              </li>
              <li>
                  <a href="{{route('log.index')}}" class="side-menu {{$active_menu=='log_list'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="plus"></i> </div>
                      <div class="side-menu__title"> Nhật ký</div>
                  </a>
              </li>
              
          </ul>
      </li>
      <!-- product category menu -->
      <li>
          <a href="javascript:;" class="side-menu {{($active_menu =='pro_add'|| $active_menu=='pro_list' || $active_menu =='brand_list' || $active_menu == 'brand_list' || $active_menu=='cat_add'|| $active_menu=='cat_list')?'side-menu--active':''}}">
              <div class="side-menu__icon"> <i data-lucide="box"></i> </div>
              <div class="side-menu__title">
                  Hàng hóa 
                  <div class="side-menu__sub-icon "> <i data-lucide="chevron-down"></i> </div>
              </div>
          </a>
          <ul class="{{($active_menu =='pro_add'|| $active_menu=='pro_list' || $active_menu =='cat_add'|| $active_menu=='cat_list' || $active_menu =='brand_list' || $active_menu == 'brand_list')?'side-menu__sub-open':''}}">
              <li>
                  <a href="{{route('product.index')}}" class="side-menu {{$active_menu=='pro_list'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="list"></i> </div>
                      <div class="side-menu__title">Danh sách hàng hóa</div>
                  </a>
              </li>
              <li>
                  <a href="{{route('product.create')}}" class="side-menu {{$active_menu=='pro_add'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="plus"></i> </div>
                      <div class="side-menu__title"> Thêm hàng hóa</div>
                  </a>
              </li>
              <li>
                  <a href="{{route('category.index')}}" class="side-menu {{$active_menu=='cat_list'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="archive"></i> </div>
                      <div class="side-menu__title"> Danh sách danh mục </div>
                  </a>
              </li>
              <li>
                  <a href="{{route('category.create')}}" class="side-menu {{$active_menu=='cat_add'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="plus"></i> </div>
                      <div class="side-menu__title"> Thêm danh mục </div>
                  </a>
              </li>
              <li>
                  <a href="{{route('brand.index')}}" class="side-menu {{$active_menu=='brand_list'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="package"></i> </div>
                      <div class="side-menu__title"> Ds nhà sản xuất </div>
                  </a>
              </li>
              <li>
                  <a href="{{route('brand.create')}}" class="side-menu {{$active_menu=='brand_add'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="plus"></i> </div>
                      <div class="side-menu__title"> Thêm nhà sản xuất </div>
                  </a>
              </li>
          </ul>
      </li>
      <!-- Nguoi dung menu  -->
      <li>
          <a href="javascript:;" class="side-menu  class="side-menu {{($active_menu =='ugroup_add'|| $active_menu=='ugroup_list' || $active_menu =='ctm_add'|| $active_menu=='ctm_list'  )?'side-menu--active':''}}">
              <div class="side-menu__icon"> <i data-lucide="user"></i> </div>
              <div class="side-menu__title">
                  Người dùng 
                  <div class="side-menu__sub-icon "> <i data-lucide="chevron-down"></i> </div>
              </div>
          </a>
          <ul class="{{($active_menu =='ugroup_add'|| $active_menu=='ugroup_list' || $active_menu =='ctm_add'|| $active_menu=='ctm_list')?'side-menu__sub-open':''}}">
              <li>
                  <a href="{{route('user.index')}}" class="side-menu {{$active_menu=='ctm_list'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="users"></i> </div>
                      <div class="side-menu__title">Danh sách người dùng</div>
                  </a>
              </li>
              <li>
                  <a href="{{route('user.create')}}" class="side-menu {{$active_menu=='ctm_add'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="plus"></i> </div>
                      <div class="side-menu__title"> Thêm người dùng</div>
                  </a>
              </li>
              <li>
                  <a href="{{route('ugroup.index')}}" class="side-menu {{$active_menu=='ugroup_list'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="circle"></i> </div>
                      <div class="side-menu__title">Ds nhóm người dùng</div>
                  </a>
              </li>
              <li>
                  <a href="{{route('ugroup.create')}}" class="side-menu {{$active_menu=='ugroup_add'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="plus"></i> </div>
                      <div class="side-menu__title"> Thêm nhóm người dùng</div>
                  </a>
              </li>
          </ul>
      </li>
      <!--Quan ly kho menu  -->
      <li>
          <a href="javascript:;" class="side-menu  class="side-menu {{($active_menu=='wm_trans' || $active_menu=='wi_trans'|| $active_menu=='sup_list'|| $active_menu=='i_list'|| $active_menu=='bi_list'|| $active_menu =='wh_add'|| $active_menu=='wh_list'    )?'side-menu--active':''}}">
              <div class="side-menu__icon"> <i data-lucide="monitor"></i> </div>
              <div class="side-menu__title">
                  Quản lý kho 
                  <div class="side-menu__sub-icon "> <i data-lucide="chevron-down"></i> </div>
              </div>
          </a>
          <ul class="{{($active_menu=='wm_trans'|| $active_menu=='wi_trans'|| $active_menu=='sup_list'||$active_menu=='wi_add'||$active_menu=='wi_list'||$active_menu=='i_list'||$active_menu=='bi_list'|| $active_menu =='wh_add'|| $active_menu=='wh_list' )?'side-menu__sub-open':''}}">
          <li>
                  <a href="{{route('warehousein.index')}}" class="side-menu {{$active_menu=='wi_list'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="corner-up-right"></i> </div>
                      <div class="side-menu__title"> Danh sách nhập kho</div>
                  </a>
              </li>      
          <li>
                  <a href="{{route('warehousein.create')}}" class="side-menu {{$active_menu=='wi_add'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="plus"></i> </div>
                      <div class="side-menu__title"> Nhập kho</div>
                  </a>
              </li>
              <li>
                  <a href="{{route('warehousetransfer.index')}}" class="side-menu {{$active_menu=='wi_trans'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="git-branch"></i> </div>
                      <div class="side-menu__title"> Chuyển kho</div>
                  </a>
              </li>
              <li>
                  <a href="{{route('warehousetomaintain.index')}}" class="side-menu {{$active_menu=='wm_trans'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="crosshair"></i> </div>
                      <div class="side-menu__title"> Chuyển kho bảo hành</div>
                  </a>
              </li>
              
              <li>
                  <a href="{{route('supplier.index')}}" class="side-menu {{$active_menu=='sup_list'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="users"></i> </div>
                      <div class="side-menu__title"> Danh sách nhà cung cấp</div>
                  </a>
              </li>
                <li>
                  <a href="{{route('warehouse.index')}}" class="side-menu {{$active_menu=='wh_list'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="figma"></i> </div>
                      <div class="side-menu__title">Danh sách kho</div>
                  </a>
              </li>
              <li>
                  <a href="{{route('warehouse.create')}}" class="side-menu {{$active_menu=='wh_add'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="plus"></i> </div>
                      <div class="side-menu__title"> Thêm kho</div>
                  </a>
              </li>
              <li>
                  <a href="{{route('binventory.index')}}" class="side-menu {{$active_menu=='bi_list'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="play"></i> </div>
                      <div class="side-menu__title"> Tồn kho đầu kì</div>
                  </a>
              </li>
              <li>
                  <a href="{{route('inventory.index')}}" class="side-menu {{$active_menu=='i_list'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="database"></i> </div>
                      <div class="side-menu__title"> Tồn kho</div>
                  </a>
              </li>
          </ul>
      </li>
       <!--Quan ly tien  -->
        <li>
          <a href="javascript:;" class="side-menu  class="side-menu {{($active_menu=='ft_list'|| $active_menu=='bt_list'||$active_menu=='bank_list'|| $active_menu=='bank_add'    )?'side-menu--active':''}}">
              <div class="side-menu__icon"> <i data-lucide="dollar-sign"></i> </div>
              <div class="side-menu__title">
                  Quản lý quỹ 
                  <div class="side-menu__sub-icon "> <i data-lucide="chevron-down"></i> </div>
              </div>
          </a>
          <ul class="{{($active_menu=='ft_list'|| $active_menu=='bt_list'|| $active_menu=='bank_list'|| $active_menu=='bank_add')?'side-menu__sub-open':''}}">
              <li>
                  <a href="{{route('bankaccount.index')}}" class="side-menu {{$active_menu=='bank_list'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="briefcase"></i> </div>
                      <div class="side-menu__title">Danh sách tài khoản</div>
                  </a>
              </li>
              <li>
                  <a href="{{route('bankaccount.create')}}" class="side-menu {{$active_menu=='bank_add'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="plus"></i> </div>
                      <div class="side-menu__title"> Thêm tài khoản</div>
                  </a>
              </li>
              <li>
                  <a href="{{route('bankaccount.viewtrans')}}" class="side-menu {{$active_menu=='bt_list'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="repeat"></i> </div>
                      <div class="side-menu__title"> Ds giao dịch tài khoản</div>
                  </a>
              </li>
              <li>
                  <a href="{{route('freetransaction.index')}}" class="side-menu {{$active_menu=='ft_list'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="wind"></i> </div>
                      <div class="side-menu__title"> Ds phiếu thu chi</div>
                  </a>
              </li>
          </ul>
      </li>
      <!--Quan ly ban hang  -->
      <li>
          <a href="javascript:;" class="side-menu  class="side-menu {{($active_menu=='or_list' || $active_menu=='customer_list'|| $active_menu=='wo_list'|| $active_menu=='wo_add'|| $active_menu=='delivery_list'    )?'side-menu--active':''}}">
              <div class="side-menu__icon"> <i data-lucide="shopping-cart"></i> </div>
              <div class="side-menu__title">
                  Quản lý bán hàng
                  <div class="side-menu__sub-icon "> <i data-lucide="chevron-down"></i> </div>
              </div>
          </a>
          <ul class="{{($active_menu=='or_list'|| $active_menu=='customer_list'|| $active_menu=='wo_list'||$active_menu=='wo_add'||$active_menu=='delivery_list' )?'side-menu__sub-open':''}}">
                <li>
                  <a href="{{route('warehouseout.index')}}" class="side-menu {{$active_menu=='wo_list'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="shopping-bag"></i> </div>
                      <div class="side-menu__title">Ds bán hàng</div>
                  </a>
              </li>
              <li>
                  <a href="{{route('warehouseout.create')}}" class="side-menu {{$active_menu=='wo_add'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="plus"></i> </div>
                      <div class="side-menu__title"> Thêm bán hàng</div>
                  </a>
              </li>
              <li>
                  <a href="{{route('order.index')}}" class="side-menu {{$active_menu=='or_list'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="shopping-cart"></i> </div>
                      <div class="side-menu__title"> Đơn đặt hàng</div>
                  </a>
              </li>
              <li>
                  <a href="{{route('customer.index')}}" class="side-menu {{$active_menu=='customer_list'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="users"></i> </div>
                      <div class="side-menu__title">Ds khách hàng</div>
                  </a>
              </li>
                <li>
                  <a href="{{route('delivery.index')}}" class="side-menu {{$active_menu=='delivery_list'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="truck"></i> </div>
                      <div class="side-menu__title">Ds nhà vận chuyển</div>
                  </a>
              </li>
             
          </ul>
      </li>
       <!--Quan ly bao hanh -->
       <li>
          <a href="javascript:;" class="side-menu  class="side-menu {{($active_menu=='mb_list' || $active_menu=='ms_list'|| $active_menu=='mainsent_list'||  $active_menu=='mainin_list'||  $active_menu=='main_inv'    )?'side-menu--active':''}}">
              <div class="side-menu__icon"> <i data-lucide="pie-chart"></i> </div>
              <div class="side-menu__title">
                  Quản lý bảo hành
                  <div class="side-menu__sub-icon "> <i data-lucide="chevron-down"></i> </div>
              </div>
          </a>
          <ul class="{{($active_menu=='mb_list' || $active_menu=='ms_list'|| $active_menu=='mainsent_list'|| $active_menu=='mainin_list'||  $active_menu=='main_inv' )?'side-menu__sub-open':''}}">
                <li>
                  <a href="{{route('inventorymaintenance.index')}}" class="side-menu {{$active_menu=='main_inv'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="codepen"></i> </div>
                      <div class="side-menu__title">Tồn kho bảo hành</div>
                  </a>
              </li>
              <li>
                  <a href="{{route('maintainin.index')}}" class="side-menu {{$active_menu=='mainin_list'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="layers"></i> </div>
                      <div class="side-menu__title">DS nhận bảo hành</div>
                  </a>
              </li>
              <li>
                  <a href="{{route('maintainsent.index')}}" class="side-menu {{$active_menu=='ms_list'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="briefcase"></i> </div>
                      <div class="side-menu__title">DS gửi bảo hành</div>
                  </a>
              </li>
              <li>
                  <a href="{{route('maintainback.index')}}" class="side-menu {{$active_menu=='mb_list'?'side-menu--active':''}}">
                      <div class="side-menu__icon"> <i data-lucide="framer"></i> </div>
                      <div class="side-menu__title">DS trả bảo hành</div>
                  </a>
              </li>
          </ul>
      </li>
  </ul>
</nav>