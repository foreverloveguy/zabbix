<?php
/*
** Zabbix
** Copyright (C) 2001-2019 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/


$this->addJsFile('multiselect.js');
$this->addJsFile('layout.mode.js');

$this->includeJSfile('app/views/monitoring.latest.view.js.php');

$web_layout_mode = CView::getLayoutMode();

$widget = (new CWidget())
	->setTitle(_('Latest data'))
	->setWebLayoutMode($web_layout_mode)
	->setControls((new CTag('nav', true,
		(new CList())
			->addItem(get_icon('fullscreen'))
		))
			->setAttribute('aria-label', _('Content controls'))
	);

if (in_array($web_layout_mode, [ZBX_LAYOUT_NORMAL, ZBX_LAYOUT_FULLSCREEN])) {
	// Filter
	$widget->addItem((new CFilter((new CUrl('zabbix.php'))->setArgument('action', 'latest.view')))
		->setProfile('web.latest.filter')
		->setActiveTab($data['active_tab'])
		->addFormItem((new CVar('action', 'latest.view'))->removeId())
		->addFilterTab(_('Filter'), [
			(new CFormList())
				->addRow((new CLabel(_('Host groups'), 'groupids__ms')),
					(new CMultiSelect([
						'name' => 'groupids[]',
						'object_name' => 'hostGroup',
						'data' => $data['multiselect_hostgroup_data'],
						'popup' => [
							'parameters' => [
								'srctbl' => 'host_groups',
								'srcfld1' => 'groupid',
								'dstfrm' => 'zbx_filter',
								'dstfld1' => 'groupids_',
								'real_hosts' => true,
								'enrich_parent_groups' => true
							]
						]
					]))->setWidth(ZBX_TEXTAREA_FILTER_STANDARD_WIDTH)
				)
				->addRow((new CLabel(_('Hosts'), 'hostids__ms')),
					(new CMultiSelect([
						'name' => 'hostids[]',
						'object_name' => 'hosts',
						'data' => $data['multiselect_host_data'],
						'popup' => [
							'parameters' => [
								'srctbl' => 'hosts',
								'srcfld1' => 'hostid',
								'dstfrm' => 'zbx_filter',
								'dstfld1' => 'hostids_'
							]
						]
					]))->setWidth(ZBX_TEXTAREA_FILTER_STANDARD_WIDTH)
				)
				->addRow(_('Application'), [
					(new CTextBox('application', $data['filter']['application']))->setWidth(ZBX_TEXTAREA_FILTER_STANDARD_WIDTH),
					(new CDiv())->addClass(ZBX_STYLE_FORM_INPUT_MARGIN),
					(new CButton('application_name', _('Select')))
						->addClass(ZBX_STYLE_BTN_GREY)
						->onClick('return PopUp("popup.generic",'.
							CJs::encodeJson([
								'srctbl' => 'applications',
								'srcfld1' => 'name',
								'dstfrm' => 'zbx_filter',
								'dstfld1' => 'application',
								'real_hosts' => '1',
								'with_applications' => '1'
							]).', null, this);'
						)
				]),
			(new CFormList())
				->addRow(_('Name'), (new CTextBox('select', $data['filter']['select']))->setWidth(ZBX_TEXTAREA_FILTER_STANDARD_WIDTH))
				->addRow(_('Show items without data'),
					(new CCheckBox('show_without_data'))->setChecked($data['filter']['showWithoutData'] == 1)
				)
				->addRow(_('Show details'), (new CCheckBox('show_details'))->setChecked($data['filter']['showDetails'] == 1))
		])
	);
}

$form_data = array_intersect_key($data, array_flip([
	'filter', 'sortField', 'sortOrder', 'hosts', 'items', 'applications', 'history', 'filterSet', 'view_url'
]));

$form_html = call_user_func(function($data) {
	return require dirname(__FILE__).'/monitoring.latest.view.data.php';
}, $form_data);

$widget->addItem($form_html)->show();
