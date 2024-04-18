
'use strict';

/**
 * メインメソッド。ここで依存性を解決する
 */

import AdditionStatus from './addition_status.js'
import Corporation from './corporation.js'
import CorporationTree from './corporation_tree.js'
import Facility from './facility.js'
import LivingRoom from './living_room.js'
import Office from './office.js'
import ServiceType from './service_type.js'
import TabManager from './tab_manager.js'
import TreeListUpdate from './tree_list_update.js'
import UninsuredService from './uninsured_service.js'
import ChangeMonitoring from '../change_monitoring.js'

document.addEventListener('DOMContentLoaded',async()=>{
  // タブマネージャー
  let tabManager = new TabManager();

  let corporatioTree = new CorporationTree();
  await corporatioTree.syncServer();

  // 法人
  let corporation = new Corporation();
  //tabManager.addNotification(corporation.elementID,corporation.setActive.bind(corporation));
  corporatioTree.addNotification(corporation.setFacilityData.bind(corporation));

  // 施設
  let facility = new Facility();
  //tabManager.addNotification(facility.elementID,facility.setActive.bind(facility));
  corporatioTree.addNotification(facility.setFacilityData.bind(facility));

  // 事業所
  let office = new Office();
  //tabManager.addNotification(office.elementID,office.setActive.bind(office));
  corporatioTree.addNotification(office.setFacilityData.bind(office));

  // サービス種別
  let serviceType = new ServiceType();
  tabManager.addNotification(serviceType.elementID,serviceType.setActive.bind(serviceType));
  corporatioTree.addNotification(serviceType.setFacilityData.bind(serviceType));

  // 加算状況
  let additionStatus = new AdditionStatus();
  tabManager.addNotification(additionStatus.elementID,additionStatus.setActive.bind(additionStatus));
  corporatioTree.addNotification(additionStatus.setFacilityData.bind(additionStatus));
  corporatioTree.addNotificationAddress(additionStatus.setFacilityRelatedData.bind(additionStatus));

  // 保険外サービス
  let uninsuredservice = new UninsuredService();
  tabManager.addNotification(uninsuredservice.elementID,uninsuredservice.setActive.bind(uninsuredservice));
  corporatioTree.addNotification(uninsuredservice.setFacilityData.bind(uninsuredservice));

  // 居室
  //let livingRoom = new LivingRoom();
  //tabManager.addNotification(livingRoom.elementID,livingRoom.setActive.bind(livingRoom));
  //corporatioTree.addNotification(livingRoom.setFacilityData.bind(livingRoom));

  // 各タブでの更新処理に対する共通部分側の更新処理
  let treeListUpdate = new TreeListUpdate();
  treeListUpdate.refreshTree(corporatioTree.refreshTree.bind(corporatioTree));

  corporatioTree.setTabManager(tabManager);
  tabManager.addChangeLayerNotificationAddress(corporatioTree.changeLayer.bind(corporatioTree));

  // サブタブの初期選択
  tabManager.subTabList[0].click();

  let cm = new ChangeMonitoring();
});
