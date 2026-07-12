<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMySqlCrud;

class BannerController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct()
    {
        $this->middleware("auth");
    }

    protected function moduleSlug(): string
    {
        return "banner-items";
    }

    public function index(): \Illuminate\View\View
    {
        $bannersCount = \DB::table('banner_items')->count();
        return view('banner_items.index', $this->moduleViewData([
            'bannersCount' => $bannersCount,
        ]));
    }

    protected function buildDatatableRow($record): array
    {
        $id = $record->id;
        $config = $this->moduleConfig();
        $canDelete = $this->userCanDeleteModule($config);

        static $placeholderImage = null;
        if ($placeholderImage === null) {
            $placeholderImage = asset('images/default_user.png');
            $placeholderRaw = \DB::table('settings')->where('id', 'placeHolderImage')->value('value');
            if ($placeholderRaw) {
                $decoded = json_decode($placeholderRaw, true);
                if (!empty($decoded['image'])) {
                    $placeholderImage = $decoded['image'];
                }
            }
        }

        $row = [];

        // 0. Checkbox
        $row[] = $canDelete
            ? '<input type="checkbox" id="is_open_' . e($id) . '" class="is_open" dataId="' . e($id) . '"><label for="is_open_' . e($id) . '"></label>'
            : '';

        // 1. Banner Info (photo + title link)
        $photoUrl = $record->photo ?: $placeholderImage;
        $editUrl = route('banners.edit', $id);
        $row[] = '<div class="banner-info-container">' .
                 '<img class="banner-img" src="' . e($photoUrl) . '" onerror="this.onerror=null;this.src=\'' . e($placeholderImage) . '\'">' .
                 '<a href="' . e($editUrl) . '" class="banner-name-link">' . e($record->title) . '</a>' .
                 '</div>';

        // 2. Banner Position
        $row[] = e($record->position ?? '');

        // 3. Publish Toggle
        $isChecked = filter_var($record->is_publish, FILTER_VALIDATE_BOOLEAN) ? 'checked' : '';
        $row[] = '<label class="switch">' .
                 '<input type="checkbox" ' . $isChecked . ' id="' . e($id) . '" name="isSwitch">' .
                 '<span class="slider round"></span>' .
                 '</label>';

        // 4. Actions
        $actions = '<div class="action-btn-container">';
        $actions .= '<a href="' . e($editUrl) . '" class="btn-circle-edit" data-toggle="tooltip" title="' . trans('lang.edit') . '"><i class="mdi mdi-lead-pencil"></i></a>';
        if ($canDelete) {
            $actions .= '<a id="' . e($id) . '" name="vendor-delete" class="btn-circle-delete ml-2" href="javascript:void(0)" data-toggle="tooltip" title="' . trans('lang.delete') . '"><i class="mdi mdi-delete"></i></a>';
        }
        $actions .= '</div>';
        $row[] = $actions;

        return $row;
    }
}
