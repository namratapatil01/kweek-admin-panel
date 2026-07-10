<?php
$settings = \DB::table('settings')->where('id', 'DriverNearBy')->first();
if ($settings) {
    $val = json_decode($settings->value, true);
    $val['selected_map_type'] = 'osm';
    \DB::table('settings')->where('id', 'DriverNearBy')->update(['value' => json_encode($val)]);
    echo "Map type updated to OSM";
}
