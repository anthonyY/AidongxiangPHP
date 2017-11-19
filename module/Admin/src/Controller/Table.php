<?php
namespace Platform\Controller;

use Platform\Model\ActivityGateway;
use Platform\Model\AdminCategoryGateway;
use Platform\Model\AdminCategoryRegionGateway;
use Platform\Model\AdminGateway;
use Platform\Model\AdsGateway;
use Platform\Model\AdsMaterialGateway;
use Platform\Model\AlbumGateway;
use Platform\Model\ArticleGateway;
use Platform\Model\BankBranchGateway;
use Platform\Model\BankListGateway;
use Platform\Model\CartGateway;
use Platform\Model\CategoryGateway;
use Platform\Model\ConsumptionRelationGateway;
use Platform\Model\ContactsGateway;
use Platform\Model\CouponGateway;
use Platform\Model\CouponUserGateway;
use Platform\Model\CourierGateway;
use Platform\Model\CustomerServiceApplyGateway;
use Platform\Model\DeviceUserGateway;
use Platform\Model\EvaluateGateway;
use Platform\Model\ExpressListGateway;
use Platform\Model\ExpressRelationGateway;
use Platform\Model\ExpressRelationRegionGateway;
use Platform\Model\ExpressRule1Gateway;
use Platform\Model\ExpressRule2Gateway;
use Platform\Model\ExpressRule3Gateway;
use Platform\Model\ExpressRule3SubGateway;
use Platform\Model\ExpressTemplateGateway;
use Platform\Model\FavoritesGateway;
use Platform\Model\FinancialGateway;
use Platform\Model\GoodsAttrGateway;
use Platform\Model\GoodsAttrRelationGateway;
use Platform\Model\GoodsFlashSaleGateway;
use Platform\Model\GoodsGateway;
use Platform\Model\GoodsLabelGateway;
use Platform\Model\GoodsPackageGateway;
use Platform\Model\GroupBuyingGoodsGateway;
use Platform\Model\ImageGateway;
use Platform\Model\LeaveMessageGateway;
use Platform\Model\LogGateway;
use Platform\Model\LoginGateway;
use Platform\Model\MerchantActivityGateway;
use Platform\Model\MerchantAppealGateway;
use Platform\Model\MerchantGateway;
use Platform\Model\MerchantLevelGateway;
use Platform\Model\ModuleGateway;
use Platform\Model\NavigationGateway;
use Platform\Model\NotificationGateway;
use Platform\Model\NotificationRecordsGateway;
use Platform\Model\OrderGateway;
use Platform\Model\OrderGoodsGateway;
use Platform\Model\PartRecommendGateway;
use Platform\Model\PayLogGateway;
use Platform\Model\PointRecordGateway;
use Platform\Model\RegionGateway;
use Platform\Model\SearchRecordGateway;
use Platform\Model\ServiceAuditingGateway;
use Platform\Model\ServiceTypeAttributeGateway;
use Platform\Model\ServiceTypeGateway;
use Platform\Model\SetupGateway;
use Platform\Model\ShoppingCardGateway;
use Platform\Model\ShoppingCardMaterialGateway;
use Platform\Model\SmsCodeGateway;
use Platform\Model\SpreadGateway;
use Platform\Model\StatisticsGateway;
use Platform\Model\UserGateway;
use Platform\Model\UserLevelGateway;
use Platform\Model\UserPartnerGateway;
use Platform\Model\ViewAdminCategoryRegionGateway;
use Platform\Model\ViewAdminGateway;
use Platform\Model\ViewAdsGateway;
use Platform\Model\ViewAlbumGateway;
use Platform\Model\ViewCartGateway;
use Platform\Model\ViewCategoryGateway;
use Platform\Model\ViewConsumptionRelationGateway;
use Platform\Model\ViewCouponGateway;
use Platform\Model\ViewCouponUserGateway;
use Platform\Model\ViewCustomerServiceApplyGateway;
use Platform\Model\ViewEvaluateGateway;
use Platform\Model\ViewExpressRelationRegionGateway;
use Platform\Model\ViewFavoritesFansGateway;
use Platform\Model\ViewFavoritesGateway;
use Platform\Model\ViewFinancialGateway;
use Platform\Model\ViewGoodsFlashSaleGateway;
use Platform\Model\ViewGoodsGateway;
use Platform\Model\ViewGroupBuyingGoodsGateway;
use Platform\Model\ViewLeaveMessageGateway;
use Platform\Model\ViewMerchantActivityGateway;
use Platform\Model\ViewMerchantAppealGateway;
use Platform\Model\ViewMerchantGateway;
use Platform\Model\ViewNavigationGateway;
use Platform\Model\ViewNotificationGateway;
use Platform\Model\ViewNotificationRecordsGateway;
use Platform\Model\ViewOrderGateway;
use Platform\Model\ViewOrderGoodsGateway;
use Platform\Model\ViewPartRecommendGateway;
use Platform\Model\ViewServiceAuditingAdsGateway;
use Platform\Model\ViewServiceAuditingGateway;
use Platform\Model\ViewServiceAuditingNotificationGateway;
use Platform\Model\ViewServiceTypeAttributeGateway;
use Platform\Model\ViewShoppingCardGateway;
use Platform\Model\ViewSpreadGateway;
use Platform\Model\ViewStatisticsGateway;
use Platform\Model\ViewUserGateway;
use Platform\Model\ViewGoodsPackageGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Mvc\Controller\AbstractActionController;
use Platform\Model\VisitorGateway;
use Platform\Model\ShoppingCardRecordGateway;
use Platform\Model\ViewShoppingCardRecordGateway;
use Platform\Model\ShareRecordGateway;
use Platform\Model\MerchantApplyGateway;
use Platform\Model\ViewMerchantApplyGateway;
use Platform\Model\ConsumerRightsProtectionApplyGateway;
use Platform\Model\ViewConsumerRightsProtectionApplyGateway;
use Platform\Model\ViewArticleGateway;
use Platform\Model\PartRecommendLabelGateway;
use Platform\Model\ViewUserGroupBuyingGateway;
use Platform\Model\ViewPartRecommendGoodsGateway;
use Platform\Model\ViewRoomTypeGateway;
use Platform\Model\ViewPreOrderProductInventoryGateway;
use Platform\Model\RoomTypeGateway;
use Platform\Model\PreOrderProductInventoryGateway;
use Platform\Model\ViewRegionGateway;

class Table extends AbstractActionController
{

    protected $adapter;
    protected $ActivityTable;
    protected $AdminTable;
    protected $AdminCategoryTable;
    protected $AdminCategoryRegionTable;
    protected $AdsTable;
    protected $AdsMaterialTable;
    protected $AlbumTable;
    protected $BankBranchTable;
    protected $BankListTable;
    protected $CartTable;
    protected $CategoryTable;
    protected $ConsumptionRelationTable;
    protected $ContactsTable;
    protected $CouponTable;
    protected $CouponUserTable;
    protected $CourierTable;
    protected $DeviceUserTable;
    protected $EvaluateTable;
    protected $ExpressListTable;
    protected $ExpressRelationTable;
    protected $ExpressRelationRegionTable;
    protected $ExpressRule1Table;
    protected $ExpressRule2Table;
    protected $ExpressRule3Table;
    protected $ExpressRule3SubTable;
    protected $ExpressTemplateTable;
    protected $FavoritesTable;
    protected $FinancialTable;
    protected $GoodsTable;
    protected $GoodsAttrTable;
    protected $GoodsAttrRelationTable;
    protected $GoodsFlashSaleTable;
    protected $GoodsLabelTable;
    protected $GoodsPackageTable;
    protected $ImageTable;
    protected $LeaveMessageTable;
    protected $LogTable;
    protected $LoginTable;
    protected $MerchantTable;
    protected $MerchantActivityTable;
    protected $MerchantAppealTable;
    protected $MerchantLevelTable;
    protected $ModuleTable;
    protected $NavigationTable;
    protected $NotificationTable;
    protected $NotificationRecordsTable;
    protected $OrderTable;
    protected $OrderGoodsTable;
    protected $PointRecordTable;
    protected $PayLogTable;
    protected $RegionTable;
    protected $SearchRecordTable;
    protected $ServiceAuditingTable;
    protected $ServiceTypeTable;
    protected $ServiceTypeAttributeTable;
    protected $SetupTable;
    protected $SmsCodeTable;
    protected $ShoppingCardTable;
    protected $ShoppingCardMaterialTable;
    protected $SpreadTable;
    protected $StatisticsTable;
    protected $UserTable;
    protected $UserLevelTable;
    protected $ViewAdminTable;
    protected $ViewAdminCategoryRegionTable;
    protected $ViewAdsTable;
    protected $ViewServiceAuditingAdsTable;
    protected $ViewAlbumTable;
    protected $ViewCartTable;
    protected $ViewCouponTable;
    protected $ViewCouponUserTable;
    protected $ViewEvaluateTable;
    protected $ViewFavoritesTable;
    protected $ViewFinancialTable;
    protected $ViewGoodsTable;
    protected $ViewGoodsFlashSaleTable;
    protected $ViewServiceAuditingTable;
    protected $ViewMerchantTable;
    protected $ViewMerchantActivityTable;
    protected $ViewMerchantAppealTable;
    protected $ViewNotificationTable;
    protected $ViewNotificationRecordsTable;
    protected $ViewServiceAuditingNotificationTable;
    protected $ViewOrderTable;
    protected $ViewOrderGoodsTable;
    protected $ViewSpreadTable;
    protected $ViewUserTable;
    protected $ViewConsumptionRelationTable;
    protected $ViewFavoritesFansTable;
    protected $ATable;
    protected $ViewNavigationTable;
    protected $ViewCustomerServiceApplyTable;
    protected $CustomerServiceApplyTable;
    protected $ViewLeaveMessageTable;
    protected $ViewCategoryTable;
    protected $ViewStatisticsTable;
    protected $ViewExpressRelationRegionTable;
    protected $ViewServiceTypeAttributeTable;
    protected $ViewGoodsPackageTable;
    protected $VisitorTable;
    protected $ViewShoppingCardTable;
    protected $ShoppingCardRecordTable;
    protected $ViewShoppingCardRecordTable;
    protected $ShareRecordTable;
    protected $MerchantApplyTable;
    protected $ViewMerchantApplyTable;
    protected $ViewPartRecommendTable;
    protected $PartRecommendTable;
    protected $ConsumerRightsProtectionApplyTable;
    protected $ViewConsumerRightsProtectionApplyTable;
    protected $GroupBuyingGoodsTable;
    protected $ViewGroupBuyingGoodsTable;

    protected $ArticleTable;
    protected $ViewArticleTable;

    protected $UserPartnerTable;
    protected $PartRecommendLabelTable;
    protected $ViewUserGroupBuyingTable;
    protected $ViewPartRecommendGoodsTable;

    protected $ViewRoomTypeTable;
    protected $ViewPreOrderProductInventoryTable;
    protected $RoomTypeTable;
    protected $PreOrderProductInventoryTable;
    protected $ViewRegionTable;
    
    public function __construct()
    {
        $driver = array(
            "driver" => "Pdo",
            "dsn" => "mysql:dbname=" . DB_NAME . ";host=" . DB_HOST,
            "username" => DB_USER,
            "password" => DB_PASSWORD,
            "charset" => DB_CHARSET,
            "driver_options" => array("1002" => "SET NAMES '" . DB_SET_NAME . "'"
            )
        );
        $adapter = new Adapter($driver);
        $this->adapter = $adapter;
    }

    protected function getActivityTable()
    {
        if (!$this->ActivityTable) {
            $this->ActivityTable = new ActivityGateway($this->adapter);
        }
        return $this->ActivityTable;
    }
    protected function getAdminTable()
    {
        if (!$this->AdminTable) {
            $this->AdminTable = new AdminGateway($this->adapter);
        }
        return $this->AdminTable;
    }

    protected function getAdminCategoryTable()
    {
        if (!$this->AdminCategoryTable) {
            $this->AdminCategoryTable = new AdminCategoryGateway($this->adapter);
        }
        return $this->AdminCategoryTable;
    }

    protected function getAdminCategoryRegionTable()
    {
        if (!$this->AdminCategoryRegionTable) {
            $this->AdminCategoryRegionTable = new AdminCategoryRegionGateway($this->adapter);
        }
        return $this->AdminCategoryRegionTable;
    }

    protected function getAdsTable()
    {
        if (!$this->AdsTable) {
            $this->AdsTable = new AdsGateway($this->adapter);
        }
        return $this->AdsTable;
    }

    protected function getAdsMaterialTable()
    {
        if (!$this->AdsMaterialTable) {
            $this->AdsMaterialTable = new AdsMaterialGateway($this->adapter);
        }
        return $this->AdsMaterialTable;
    }

    protected function getAlbumTable()
    {
        if (!$this->AlbumTable) {
            $this->AlbumTable = new AlbumGateway($this->adapter);
        }
        return $this->AlbumTable;
    }

    protected function getBankBranchTable()
    {
        if (!$this->BankBranchTable) {
            $this->BankBranchTable = new BankBranchGateway($this->adapter);
        }
        return $this->BankBranchTable;
    }

    protected function getBankListTable()
    {
        if (!$this->BankListTable) {
            $this->BankListTable = new BankListGateway($this->adapter);
        }
        return $this->BankListTable;
    }

    protected function getCartTable()
    {
        if (!$this->CartTable) {
            $this->CartTable = new CartGateway($this->adapter);
        }
        return $this->CartTable;
    }

    protected function getCategoryTable()
    {
        if (!$this->CategoryTable) {
            $this->CategoryTable = new CategoryGateway($this->adapter);
        }
        return $this->CategoryTable;
    }

    protected function getConsumptionRelationTable()
    {
        if(!$this->ConsumptionRelationTable){
            $this->ConsumptionRelationTable = new ConsumptionRelationGateway($this->adapter);
        }
        return $this->ConsumptionRelationTable;

    }
    protected function getContactsTable()
    {
        if (!$this->ContactsTable) {
            $this->ContactsTable = new ContactsGateway($this->adapter);
        }
        return $this->ContactsTable;
    }

    protected function getCouponTable()
    {
        if (!$this->CouponTable) {
            $this->CouponTable = new CouponGateway($this->adapter);
        }
        return $this->CouponTable;
    }

    protected function getCouponUserTable()
    {
        if (!$this->CouponUserTable) {
            $this->CouponUserTable = new CouponUserGateway($this->adapter);
        }
        return $this->CouponUserTable;
    }

    protected function getCourierTable()
    {
        if (!$this->CourierTable) {
            $this->CourierTable = new CourierGateway($this->adapter);
        }
        return $this->CourierTable;
    }

    protected function getDeviceUserTable()
    {
        if (!$this->DeviceUserTable) {
            $this->DeviceUserTable = new DeviceUserGateway($this->adapter);
        }
        return $this->DeviceUserTable;
    }

    protected function getEvaluateTable()
    {
        if (!$this->EvaluateTable) {
            $this->EvaluateTable = new EvaluateGateway($this->adapter);
        }
        return $this->EvaluateTable;
    }

    protected function getExpressListTable()
    {
        if (!$this->ExpressListTable) {
            $this->ExpressListTable = new ExpressListGateway($this->adapter);
        }
        return $this->ExpressListTable;
    }

    protected function getExpressRelationTable()
    {
        if (!$this->ExpressRelationTable) {
            $this->ExpressRelationTable = new ExpressRelationGateway($this->adapter);
        }
        return $this->ExpressRelationTable;
    }

    protected function getExpressRelationRegionTable()
    {
        if (!$this->ExpressRelationRegionTable) {
            $this->ExpressRelationRegionTable = new ExpressRelationRegionGateway($this->adapter);
        }
        return $this->ExpressRelationRegionTable;
    }

    protected function getExpressRule1Table()
    {
        if (!$this->ExpressRule1Table) {
            $this->ExpressRule1Table = new ExpressRule1Gateway($this->adapter);
        }
        return $this->ExpressRule1Table;
    }

    protected function getExpressRule2Table()
    {
        if (!$this->ExpressRule2Table) {
            $this->ExpressRule2Table = new ExpressRule2Gateway($this->adapter);
        }
        return $this->ExpressRule2Table;
    }

    protected function getExpressRule3Table()
    {
        if (!$this->ExpressRule3Table) {
            $this->ExpressRule3Table = new ExpressRule3Gateway($this->adapter);
        }
        return $this->ExpressRule3Table;
    }
    
    protected function getExpressRule3SubTable()
    {
        if (!$this->ExpressRule3SubTable) {
            $this->ExpressRule3SubTable = new ExpressRule3SubGateway($this->adapter);
        }
        return $this->ExpressRule3SubTable;
    }

    protected function getExpressTemplateTable()
    {
        if (!$this->ExpressTemplateTable) {
            $this->ExpressTemplateTable = new ExpressTemplateGateway($this->adapter);
        }
        return $this->ExpressTemplateTable;
    }

    protected function getFavoritesTable()
    {
        if (!$this->FavoritesTable) {
            $this->FavoritesTable = new FavoritesGateway($this->adapter);
        }
        return $this->FavoritesTable;
    }

    protected function getFinancialTable()
    {
        if (!$this->FinancialTable) {
            $this->FinancialTable = new FinancialGateway($this->adapter);
        }
        return $this->FinancialTable;
    }

    protected function getGoodsTable()
    {
        if (!$this->GoodsTable) {
            $this->GoodsTable = new GoodsGateway($this->adapter);
        }
        return $this->GoodsTable;
    }

    protected function getGoodsAttrTable()
    {
        if (!$this->GoodsAttrTable) {
            $this->GoodsAttrTable = new GoodsAttrGateway($this->adapter);
        }
        return $this->GoodsAttrTable;
    }

    protected function getGoodsAttrRelationTable()
    {
        if (!$this->GoodsAttrRelationTable) {
            $this->GoodsAttrRelationTable = new GoodsAttrRelationGateway($this->adapter);
        }
        return $this->GoodsAttrRelationTable;
    }

    protected function getGoodsFlashSaleTable()
    {
        if (!$this->GoodsFlashSaleTable) {
            $this->GoodsFlashSaleTable = new GoodsFlashSaleGateway($this->adapter);
        }
        return $this->GoodsFlashSaleTable;
    }

    protected function getGoodsLabelTable()
    {
        if (!$this->GoodsLabelTable) {
            $this->GoodsLabelTable = new GoodsLabelGateway($this->adapter);
        }
        return $this->GoodsLabelTable;
    }

    protected function getGoodsPackageTable()
    {
        if (!$this->GoodsPackageTable) {
            $this->GoodsPackageTable = new GoodsPackageGateway($this->adapter);
        }
        return $this->GoodsPackageTable;
    }

    protected function getImageTable()
    {
        if (!$this->ImageTable) {
            $this->ImageTable = new ImageGateway($this->adapter);
        }
        return $this->ImageTable;
    }

    protected function getLeaveMessageTable()
    {
        if (!$this->LeaveMessageTable) {
            $this->LeaveMessageTable = new LeaveMessageGateway($this->adapter);
        }
        return $this->LeaveMessageTable;
    }

    protected function getLogTable()
    {
        if (!$this->LogTable) {
            $this->LogTable = new LogGateway($this->adapter);
        }
        return $this->LogTable;
    }

    protected function getLoginTable()
    {
        if (!$this->LoginTable) {
            $this->LoginTable = new LoginGateway($this->adapter);
        }
        return $this->LoginTable;
    }

    protected function getMerchantTable()
    {
        if (!$this->MerchantTable) {
            $this->MerchantTable = new MerchantGateway($this->adapter);
        }
        return $this->MerchantTable;
    }

    protected function getMerchantActivityTable()
    {
        if (!$this->MerchantActivityTable) {
            $this->MerchantActivityTable = new MerchantActivityGateway($this->adapter);
        }
        return $this->MerchantActivityTable;
    }

    protected function getMerchantAppealTable()
    {
        if (!$this->MerchantAppealTable) {
            $this->MerchantAppealTable = new MerchantAppealGateway($this->adapter);
        }
        return $this->MerchantAppealTable;
    }

    protected function getMerchantLevelTable()
    {
        if (!$this->MerchantLevelTable) {
            $this->MerchantLevelTable = new MerchantLevelGateway($this->adapter);
        }
        return $this->MerchantLevelTable;
    }

    protected function getModuleTable()
    {
        if (!$this->ModuleTable) {
            $this->ModuleTable = new ModuleGateway($this->adapter);
        }
        return $this->ModuleTable;
    }

    protected function getNavigationTable()
    {
        if (!$this->NavigationTable) {
            $this->NavigationTable = new NavigationGateway($this->adapter);
        }
        return $this->NavigationTable;
    }

    protected function getNotificationTable()
    {
        if (!$this->NotificationTable) {
            $this->NotificationTable = new NotificationGateway($this->adapter);
        }
        return $this->NotificationTable;
    }

    protected function getNotificationRecordsTable()
    {
        if (!$this->NotificationRecordsTable) {
            $this->NotificationRecordsTable = new NotificationRecordsGateway($this->adapter);
        }
        return $this->NotificationRecordsTable;
    }

    protected function getOrderTable()
    {
        if (!$this->OrderTable) {
            $this->OrderTable = new OrderGateway($this->adapter);
        }
        return $this->OrderTable;
    }

    protected function getOrderGoodsTable()
    {
        if (!$this->OrderGoodsTable) {
            $this->OrderGoodsTable = new OrderGoodsGateway($this->adapter);
        }
        return $this->OrderGoodsTable;
    }

    protected function getPayLogTable()
    {
        if (!$this->PayLogTable) {
            $this->PayLogTable = new PayLogGateway($this->adapter);
        }
        return $this->PayLogTable;
    }

    protected function getPointRecordTable()
    {
        if (!$this->PointRecordTable) {
            $this->PointRecordTable = new PointRecordGateway($this->adapter);
        }
        return $this->PointRecordTable;
    }

    protected function getRegionTable()
    {
        if (!$this->RegionTable) {
            $this->RegionTable = new RegionGateway($this->adapter);
        }
        return $this->RegionTable;
    }

    protected function getSearchRecordTable()
    {
        if (!$this->SearchRecordTable) {
            $this->SearchRecordTable = new SearchRecordGateway($this->adapter);
        }
        return $this->SearchRecordTable;
    }

    protected function getServiceAuditingTable()
    {
        if (!$this->ServiceAuditingTable) {
            $this->ServiceAuditingTable = new ServiceAuditingGateway($this->adapter);
        }
        return $this->ServiceAuditingTable;
    }

    protected function getServiceTypeTable()
    {
        if (!$this->ServiceTypeTable) {
            $this->ServiceTypeTable = new ServiceTypeGateway($this->adapter);
        }
        return $this->ServiceTypeTable;
    }

    protected function getServiceTypeAttributeTable()
    {
        if (!$this->ServiceTypeAttributeTable) {
            $this->ServiceTypeAttributeTable = new ServiceTypeAttributeGateway($this->adapter);
        }
        return $this->ServiceTypeAttributeTable;
    }

    protected function getSetupTable()
    {
        if (!$this->SetupTable) {
            $this->SetupTable = new SetupGateway($this->adapter);
        }
        return $this->SetupTable;
    }

    protected function getSpreadTable()
    {
        if (!$this->SpreadTable) {
            $this->SpreadTable = new SpreadGateway($this->adapter);
        }
        return $this->SpreadTable;
    }

    protected function getStatisticsTable()
    {
        if (!$this->StatisticsTable) {
            $this->StatisticsTable = new StatisticsGateway($this->adapter);
        }
        return $this->StatisticsTable;
    }

    protected function getShoppingCardTable()
    {
        if(!$this->ShoppingCardTable){
            $this->ShoppingCardTable = new ShoppingCardGateway($this->adapter);
        }
        return $this->ShoppingCardTable;
    }

    protected function getShoppingCardMaterialTable()
    {
        if(!$this->ShoppingCardMaterialTable){
            $this->ShoppingCardMaterialTable = new ShoppingCardMaterialGateway($this->adapter);
        }
        return $this->ShoppingCardMaterialTable;
    }

    protected function getSmsCodeTable()
    {
        if (!$this->SmsCodeTable) {
            $this->SmsCodeTable = new SmsCodeGateway($this->adapter);
        }
        return $this->SmsCodeTable;
    }

    protected function getUserTable()
    {
        if (!$this->UserTable) {
            $this->UserTable = new UserGateway($this->adapter);
        }
        return $this->UserTable;
    }

    protected function getUserLevelTable()
    {
        if (!$this->UserLevelTable) {
            $this->UserLevelTable = new UserLevelGateway($this->adapter);
        }
        return $this->UserLevelTable;
    }

    protected function getViewAdminTable()
    {
        if (!$this->ViewAdminTable) {
            $this->ViewAdminTable = new ViewAdminGateway($this->adapter);
        }
        return $this->ViewAdminTable;
    }

    protected function getViewAdminCategoryRegionTable()
    {
        if (!$this->ViewAdminCategoryRegionTable) {
            $this->ViewAdminCategoryRegionTable = new ViewAdminCategoryRegionGateway($this->adapter);
        }
        return $this->ViewAdminCategoryRegionTable;
    }

    protected function getViewAdsTable()
    {
        if (!$this->ViewAdsTable) {
            $this->ViewAdsTable = new ViewAdsGateway($this->adapter);
        }
        return $this->ViewAdsTable;
    }

    protected function getViewServiceAuditingAdsTable()
    {
        if (!$this->ViewServiceAuditingAdsTable) {
            $this->ViewServiceAuditingAdsTable = new ViewServiceAuditingAdsGateway($this->adapter);
        }
        return $this->ViewServiceAuditingAdsTable;
    }

    protected function getViewAlbumTable()
    {
        if (!$this->ViewAlbumTable) {
            $this->ViewAlbumTable = new ViewAlbumGateway($this->adapter);
        }
        return $this->ViewAlbumTable;
    }

    protected function getViewCartTable()
    {
        if (!$this->ViewCartTable) {
            $this->ViewCartTable = new ViewCartGateway($this->adapter);
        }
        return $this->ViewCartTable;
    }

    protected function getViewCouponTable()
    {
        if (!$this->ViewCouponTable) {
            $this->ViewCouponTable = new ViewCouponGateway($this->adapter);
        }
        return $this->ViewCouponTable;
    }

    protected function getViewCouponUserTable()
    {
        if (!$this->ViewCouponUserTable) {
            $this->ViewCouponUserTable = new ViewCouponUserGateway($this->adapter);
        }
        return $this->ViewCouponUserTable;
    }

    protected function getViewEvaluateTable()
    {
        if (!$this->ViewEvaluateTable) {
            $this->ViewEvaluateTable = new ViewEvaluateGateway($this->adapter);
        }
        return $this->ViewEvaluateTable;
    }

    protected function getViewFavoritesTable()
    {
        if (!$this->ViewFavoritesTable) {
            $this->ViewFavoritesTable = new ViewFavoritesGateway($this->adapter);
        }
        return $this->ViewFavoritesTable;
    }

    protected function getViewFinancialTable()
    {
        if (!$this->ViewFinancialTable) {
            $this->ViewFinancialTable = new ViewFinancialGateway($this->adapter);
        }
        return $this->ViewFinancialTable;
    }

    protected function getViewGoodsTable()
    {
        if (!$this->ViewGoodsTable) {
            $this->ViewGoodsTable = new ViewGoodsGateway($this->adapter);
        }
        return $this->ViewGoodsTable;
    }

    protected function getViewGoodsFlashSaleTable()
    {
        if (!$this->ViewGoodsFlashSaleTable) {
            $this->ViewGoodsFlashSaleTable = new ViewGoodsFlashSaleGateway($this->adapter);
        }
        return $this->ViewGoodsFlashSaleTable;
    }

    protected function getViewServiceAuditingTable()
    {
        if (!$this->ViewServiceAuditingTable) {
            $this->ViewServiceAuditingTable = new ViewServiceAuditingGateway($this->adapter);
        }
        return $this->ViewServiceAuditingTable;
    }

    protected function getViewMerchantTable()
    {
        if (!$this->ViewMerchantTable) {
            $this->ViewMerchantTable = new ViewMerchantGateway($this->adapter);
        }
        return $this->ViewMerchantTable;
    }

    protected function getViewMerchantActivityTable()
    {
        if (!$this->ViewMerchantActivityTable) {
            $this->ViewMerchantActivityTable = new ViewMerchantActivityGateway($this->adapter);
        }
        return $this->ViewMerchantActivityTable;
    }

    protected function getViewMerchantAppealTable()
    {
        if (!$this->ViewMerchantAppealTable) {
            $this->ViewMerchantAppealTable = new ViewMerchantAppealGateway($this->adapter);
        }
        return $this->ViewMerchantAppealTable;
    }

    protected function getViewNotificationTable()
    {
        if (!$this->ViewNotificationTable) {
            $this->ViewNotificationTable = new ViewNotificationGateway($this->adapter);
        }
        return $this->ViewNotificationTable;
    }

    protected function getViewNotificationRecordsTable()
    {
        if (!$this->ViewNotificationRecordsTable) {
            $this->ViewNotificationRecordsTable = new ViewNotificationRecordsGateway($this->adapter);
        }
        return $this->ViewNotificationRecordsTable;
    }

    protected function getViewServiceAuditingNotificationTable()
    {
        if (!$this->ViewServiceAuditingNotificationTable) {
            $this->ViewServiceAuditingNotificationTable = new ViewServiceAuditingNotificationGateway($this->adapter);
        }
        return $this->ViewServiceAuditingNotificationTable;
    }

    protected function getViewOrderTable()
    {
        if (!$this->ViewOrderTable) {
            $this->ViewOrderTable = new ViewOrderGateway($this->adapter);
        }
        return $this->ViewOrderTable;
    }

    protected function getViewOrderGoodsTable()
    {
        if (!$this->ViewOrderGoodsTable) {
            $this->ViewOrderGoodsTable = new ViewOrderGoodsGateway($this->adapter);
        }
        return $this->ViewOrderGoodsTable;
    }

    protected function getViewSpreadTable()
    {
        if (!$this->ViewSpreadTable) {
            $this->ViewSpreadTable = new ViewSpreadGateway($this->adapter);
        }
        return $this->ViewSpreadTable;
    }

    protected function getViewUserTable()
    {
        if (!$this->ViewUserTable) {
            $this->ViewUserTable = new ViewUserGateway($this->adapter);
        }
        return $this->ViewUserTable;
    }

    protected function getViewConsumptionRelationTable()
    {
        if(!$this->ViewConsumptionRelationTable){
            $this->ViewConsumptionRelationTable = new ViewConsumptionRelationGateway($this->adapter);
        }
        return $this->ViewConsumptionRelationTable;
    }

    protected function getViewFavoritesFansTable()
    {
        if(!$this->ViewFavoritesFansTable){
            $this->ViewFavoritesFansTable = new ViewFavoritesFansGateway($this->adapter);
        }
        return $this->ViewFavoritesFansTable;
    }

    protected function getViewNavigationTable()
    {
        if (!$this->ViewNavigationTable) {
            $this->ViewNavigationTable = new ViewNavigationGateway($this->adapter);
        }
        return $this->ViewNavigationTable;
    }

    protected function getCustomerServiceApplyTable()
    {
        if (!$this->CustomerServiceApplyTable) {
            $this->CustomerServiceApplyTable = new CustomerServiceApplyGateway($this->adapter);
        }
        return $this->CustomerServiceApplyTable;
    }

    protected function getViewCustomerServiceApplyTable()
    {
        if (!$this->ViewCustomerServiceApplyTable) {
            $this->ViewCustomerServiceApplyTable = new ViewCustomerServiceApplyGateway($this->adapter);
        }
        return $this->ViewCustomerServiceApplyTable;
    }

    protected function getViewLeaveMessageTable()
    {
        if (!$this->ViewLeaveMessageTable) {
            $this->ViewLeaveMessageTable = new ViewLeaveMessageGateway($this->adapter);
        }
        return $this->ViewLeaveMessageTable;
    }

    protected function getViewCategoryTable()
    {
        if(!$this->ViewCategoryTable)
        {
            $this->ViewCategoryTable = new ViewCategoryGateway($this->adapter);
        }
        return $this->ViewCategoryTable;
    }
    protected function getViewStatisticsTable()
    {
        if (!$this->ViewStatisticsTable) {
            $this->ViewStatisticsTable = new ViewStatisticsGateway($this->adapter);
        }
        return $this->ViewStatisticsTable;
    }

    protected function getViewExpressRelationRegionTable()
    {
        if (!$this->ViewExpressRelationRegionTable) {
            $this->ViewExpressRelationRegionTable= new ViewExpressRelationRegionGateway($this->adapter);
        }
        return $this->ViewExpressRelationRegionTable;

    }

    protected function getViewServiceTypeAttributeTable()
    {
        if (!$this->ViewServiceTypeAttributeTable) {
            $this->ViewServiceTypeAttributeTable = new ViewServiceTypeAttributeGateway($this->adapter);
        }
        return $this->ViewServiceTypeAttributeTable;

    }

    protected function getViewGoodsPackageTable()
    {
        if (!$this->ViewGoodsPackageTable) {
            $this->ViewGoodsPackageTable = new ViewGoodsPackageGateway($this->adapter);
        }
        return $this->ViewGoodsPackageTable;

    }

    protected function getVisitorTable()
    {
        if (!$this->VisitorTable) {
            $this->VisitorTable = new VisitorGateway($this->adapter);
        }
        return $this->VisitorTable;
    }

    protected function getViewShoppingCardTable()
    {
        if(!$this->ViewShoppingCardTable){
            $this->ViewShoppingCardTable = new ViewShoppingCardGateway($this->adapter);
        }
        return $this->ViewShoppingCardTable;
    }

    protected function getShoppingCardRecordTable()
    {
        if(!$this->ShoppingCardRecordTable){
            $this->ShoppingCardRecordTable = new ShoppingCardRecordGateway($this->adapter);
        }
        return $this->ShoppingCardRecordTable;
    }

    protected function getViewShoppingCardRecordTable()
    {
        if(!$this->ViewShoppingCardRecordTable){
            $this->ViewShoppingCardRecordTable = new ViewShoppingCardRecordGateway($this->adapter);
        }
        return $this->ViewShoppingCardRecordTable;
    }

    protected function getShareRecordTable()
    {
        if(!$this->ShareRecordTable){
            $this->ShareRecordTable = new ShareRecordGateway($this->adapter);
        }
        return $this->ShareRecordTable;
    }

    protected function getMerchantApplyTable()
    {
        if(!$this->MerchantApplyTable){
            $this->MerchantApplyTable = new MerchantApplyGateway($this->adapter);
        }
        return $this->MerchantApplyTable;
    }

    protected function getViewMerchantApplyTable()
    {
        if(!$this->ViewMerchantApplyTable){
            $this->ViewMerchantApplyTable = new ViewMerchantApplyGateway($this->adapter);
        }
        return $this->ViewMerchantApplyTable;
    }

    protected function getPartRecommendTable()
    {
        if(!$this->PartRecommendTable){
            $this->PartRecommendTable = new PartRecommendGateway($this->adapter);
        }
        return $this->PartRecommendTable;
    }
    
    protected function getViewPartRecommendTable()
    {
        if(!$this->ViewPartRecommendTable){
            $this->ViewPartRecommendTable = new ViewPartRecommendGateway($this->adapter);
        }
        return $this->ViewPartRecommendTable;
    }
    
    protected function getConsumerRightsProtectionApplyTable()
    {
        if(!$this->ConsumerRightsProtectionApplyTable){
            $this->ConsumerRightsProtectionApplyTable = new ConsumerRightsProtectionApplyGateway($this->adapter);
        }
        return $this->ConsumerRightsProtectionApplyTable;
    }

    protected function getViewConsumerRightsProtectionApplyTable()
    {
        if(!$this->ViewConsumerRightsProtectionApplyTable){
            $this->ViewConsumerRightsProtectionApplyTable = new ViewConsumerRightsProtectionApplyGateway($this->adapter);
        }
        return $this->ViewConsumerRightsProtectionApplyTable;
    }

    protected function getUserPartnerTable()
    {
        if(!$this->UserPartnerTable){
            $this->UserPartnerTable = new UserPartnerGateway($this->adapter);
        }
        return $this->UserPartnerTable;
    }
    
    protected function getArticleTable()
    {
        if(!$this->ArticleTable){
            $this->ArticleTable = new ArticleGateway($this->adapter);
        }
        return $this->ArticleTable;
    }

    protected function getViewArticleTable()
    {
        if(!$this->ViewArticleTable){
            $this->ViewArticleTable = new ViewArticleGateway($this->adapter);
        }
        return $this->ViewArticleTable;
    }

    protected function getGroupBuyingGoodsTable()
    {
        if(!$this->GroupBuyingGoodsTable){
            $this->GroupBuyingGoodsTable = new GroupBuyingGoodsGateway($this->adapter);
        }
        return $this->GroupBuyingGoodsTable;
    }


    protected function getViewGroupBuyingGoodsTable()
    {
        if (!$this->ViewGroupBuyingGoodsTable) {
            $this->ViewGroupBuyingGoodsTable = new ViewGroupBuyingGoodsGateway($this->adapter);
        }
        return $this->ViewGroupBuyingGoodsTable;
    }

    protected function getPartRecommendLabelTable()
    {
        if (!$this->PartRecommendLabelTable) {
            $this->PartRecommendLabelTable = new PartRecommendLabelGateway($this->adapter);
        }
        return $this->PartRecommendLabelTable;
    }

    protected function getViewUserGroupBuyingTable()
    {
        if (!$this->ViewUserGroupBuyingTable) {
            $this->ViewUserGroupBuyingTable = new ViewUserGroupBuyingGateway($this->adapter);
        }
        return $this->ViewUserGroupBuyingTable;
    }

    protected function getViewPartRecommendGoodsTable()
    {
        if (!$this->ViewPartRecommendGoodsTable) {
            $this->ViewPartRecommendGoodsTable = new ViewPartRecommendGoodsGateway($this->adapter);
        }
        return $this->ViewPartRecommendGoodsTable;
    }


    protected function getViewRoomTypeTable()
    {
        if (!$this->ViewRoomTypeTable) {
            $this->ViewRoomTypeTable = new ViewRoomTypeGateway($this->adapter);
        }
        return $this->ViewRoomTypeTable;
    }

    protected function getViewPreOrderProductInventoryTable()
    {
        if (!$this->ViewPreOrderProductInventoryTable) {
            $this->ViewPreOrderProductInventoryTable = new ViewPreOrderProductInventoryGateway($this->adapter);
        }
        return $this->ViewPreOrderProductInventoryTable;
    }

    protected function getRoomTypeTable()
    {
        if (!$this->RoomTypeTable) {
            $this->RoomTypeTable = new RoomTypeGateway($this->adapter);
        }
        return $this->RoomTypeTable;
    }

    protected function getPreOrderProductInventoryTable()
    {
        if (!$this->PreOrderProductInventoryTable) {
            $this->PreOrderProductInventoryTable = new PreOrderProductInventoryGateway($this->adapter);
        }
        return $this->PreOrderProductInventoryTable;
    }

    protected function getViewRegionTable()
    {
        if (!$this->ViewRegionTable) {
            $this->ViewRegionTable = new ViewRegionGateway($this->adapter);
        }
        return $this->ViewRegionTable;
    }

}