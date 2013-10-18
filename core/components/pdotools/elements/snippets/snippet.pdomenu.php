<?php
/** @var array $scriptProperties */

// Convert parameters from Wayfinder if exists
if (isset($startId)) {
	$scriptProperties['parents'] = $startId;
}
if (!empty($includeDocs)) {
	$tmp = array_map('trim', explode(',', $includeDocs));
	foreach ($tmp as $v) {
		if (!empty($scriptProperties['parents'])) {
			$scriptProperties['parents'] .= ','.$v;
		}
		else {
			$scriptProperties['parents'] = $v;
		}
	}
}
if (!empty($excludeDocs)) {
	$tmp = array_map('trim', explode(',', $excludeDocs));
	foreach ($tmp as $v) {
		if (!empty($scriptProperties['parents'])) {
			$scriptProperties['parents'] .= ',-'.$v;
		}
		else {
			$scriptProperties['parents'] = '-'.$v;
		}
	}
}
if ($scriptProperties['parents'] === '') {
	$scriptProperties['parents'] = $modx->resource->id;
}

if (isset($level)) {
	$scriptProperties['depth'] = empty($level)
		? $scriptProperties['depth'] = 100
		:$level - 1;
}
if ($scriptProperties['parents'] === 0 || $scriptProperties['parents'] === '0') {
	$scriptProperties['includeParents'] = 1;
	$scriptProperties['context'] = $modx->resource->context_key;

	if ($scriptProperties['depth'] !== '' && $scriptProperties['depth'] !== 100) {
		$parents = $modx->getChildIds(0, $scriptProperties['depth'], array('context' => $modx->resource->context_key));
		$scriptProperties['parents'] = !empty($parents)
			? implode(',', $parents)
			: '+0';
	}
}

if (!empty($scriptProperties['previewUnpublished']) && $modx->hasPermission('view_unpublished')) {
	$scriptProperties['showUnpublished'] = 1;
}

if (!empty($displayStart)) {$scriptProperties['includeParents'] = 1;}
if (!empty($ph)) {$toPlaceholder = $ph;}
if (!empty($sortOrder)) {$scriptProperties['sortdir'] = $sortOrder;}
if (!empty($sortBy)) {$scriptProperties['sortby'] = $sortBy;}
if (!empty($permissions)) {$scriptProperties['checkPermissions'] = $permissions;}
if (!empty($contexts)) {$scriptProperties['context'] = $contexts;}

$wfTemplates = array(
	'outerTpl' => 'tplOuter',
	'rowTpl' => 'tpl',
	'parentRowTpl' => 'tplParentRow',
	'parentRowHereTpl' => 'tplParentRowHere',
	'hereTpl' => 'tplHere',
	'innerTpl' => 'tplInner',
	'innerRowTpl' => 'tplInnerRow',
	'innerHereTpl' => 'tplInnerHere',
	'activeParentRowTpl' => 'tplParentRowActive',
	'categoryFoldersTpl' => 'tplCategoryFolder',
	'startItemTpl' => 'tplStart'
);
foreach ($wfTemplates as $k => $v) {
	if (isset(${$k})) {
		$scriptProperties[$v] = ${$k};
	}
}

//---

/** @var pdoMenu $pdoMenu */
if (!$modx->loadClass('pdoMenu', MODX_CORE_PATH . 'components/pdotools/model/pdotools/', false, true)) {return false;}
$pdoMenu = new pdoMenu($modx, $scriptProperties);
$pdoMenu->addTime('pdoTools loaded');

$rows = $pdoMenu->run();
$tree = $pdoMenu->buildTree($rows);
$output = $pdoMenu->templateTree($tree);

if ($modx->user->hasSessionContext('mgr') && !empty($showLog)) {
	$output .= '<pre class="pdoMenuLog">' . print_r($pdoMenu->getTime(), 1) . '</pre>';
}

if (!empty($toPlaceholder)) {
	$modx->setPlaceholder($toPlaceholder, $output);
}
else {
	return $output;
}