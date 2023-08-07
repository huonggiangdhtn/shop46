 <!-- BEGIN: Dark Mode Switcher-->
      
        <!-- END: Dark Mode Switcher-->
        
        <!-- BEGIN: JS Assets-->
        <script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
        {{-- <script src="https://maps.googleapis.com/maps/api/js?key=["your-google-map-api"]&libraries=places"></script> --}}
        <script src="{{asset('backend/assets/dist/js/app.js')}}"></script>
        <script src="{{asset('backend/assets/vendor/libs/jquery/jquery.js')}}"></script> 
        <!-- END: JS Assets-->
       
        @yield('scripts')