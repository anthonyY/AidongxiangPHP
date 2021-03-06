<?php
namespace Admin\Controller;
        
use Admin\Model\ViewReportGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Mvc\Controller\AbstractActionController;
use Admin\Model\AdminGateway;
use Admin\Model\AdminCategoryGateway;
use Admin\Model\AdsGateway;
use Admin\Model\AlbumGateway;
use Admin\Model\ArticleGateway;
use Admin\Model\AudioGateway;
use Admin\Model\BuyLogGateway;
use Admin\Model\CategoryGateway;
use Admin\Model\CommentGateway;
use Admin\Model\DeviceUserGateway;
use Admin\Model\DownloadGateway;
use Admin\Model\FavoriteGateway;
use Admin\Model\FinancialGateway;
use Admin\Model\FocusRelationGateway;
use Admin\Model\ImageGateway;
use Admin\Model\LabelGateway;
use Admin\Model\LoginGateway;
use Admin\Model\MicroblogGateway;
use Admin\Model\MobileAppealGateway;
use Admin\Model\ModuleGateway;
use Admin\Model\NavigationGateway;
use Admin\Model\NotificationGateway;
use Admin\Model\NotificationRecordsGateway;
use Admin\Model\PraiseGateway;
use Admin\Model\RegionGateway;
use Admin\Model\ReportGateway;
use Admin\Model\ScreenGateway;
use Admin\Model\SetupGateway;
use Admin\Model\SmsCodeGateway;
use Admin\Model\UserGateway;
use Admin\Model\UserPartnerGateway;
use Admin\Model\WatchRecordGateway;
use Admin\Model\ViewAdminGateway;
use Admin\Model\ViewUserGateway;
use Admin\Model\ViewAdsGateway;
use Admin\Model\ViewNavigationGateway;
use Admin\Model\ViewMobileAppealGateway;
use Admin\Model\ViewAudioGateway;
use Admin\Model\ViewCategoryGateway;
use Admin\Model\ViewMicroblogGateway;
use Admin\Model\ViewAlbumGateway;
use Admin\Model\ViewArticleGateway;
use Admin\Model\ViewCommentGateway;
use Admin\Model\ViewFinancialGateway;
use Admin\Model\ViewFavoriteGateway;
use Admin\Model\ViewWatchRecordGateway;
use Admin\Model\ViewFocusRelationGateway;
        
class Table extends AbstractActionController
{
        
    public $adapter;
    protected $AdminTable;protected $AdminCategoryTable;protected $AdsTable;protected $AlbumTable;protected $ArticleTable;protected $AudioTable;protected $BuyLogTable;protected $CategoryTable;protected $CommentTable;protected $DeviceUserTable;protected $DownloadTable;protected $FavoriteTable;protected $FinancialTable;protected $FocusRelationTable;protected $ImageTable;protected $LabelTable;protected $LoginTable;protected $MicroblogTable;protected $MobileAppealTable;protected $ModuleTable;protected $NavigationTable;protected $NotificationTable;protected $NotificationRecordsTable;protected $PraiseTable;protected $RegionTable;protected $ReportTable;protected $ScreenTable;protected $SetupTable;protected $SmsCodeTable;protected $UserTable;protected $UserPartnerTable;protected $WatchRecordTable;
    protected $ViewAdminTable;
    protected $ViewUserTable;
    protected $ViewAdsTable;
    protected $ViewNavigationTable;
    protected $ViewMobileAppealTable;
    protected $ViewAudioTable;
    protected $ViewCategoryTable;
    protected $ViewMicroblogTable;
    protected $ViewAlbumTable;
    protected $ViewArticleTable;
    protected $ViewCommentTable;
    protected $ViewFinancialTable;
    protected $ViewFavoriteTable;
    protected $ViewWatchRecordTable;
    protected $ViewFocusRelationTable;
    protected $ViewReportTable;

    public function __construct()
    {
        $driver = array(
            "driver" => "Pdo",
            "dsn" => "mysql:dbname=" . DB_NAME . ";host=" . DB_HOST,
            "username" => DB_USER,
            "password" => DB_PASSWORD,
            "charset" => DB_CHARSET,
            "driver_options" => array(
                "1002" => "SET NAMES '".DB_SET_NAME."'"
            )
        );
        $adapter = new Adapter($driver);
        $this->adapter = $adapter;
    }protected function getAdminTable()
    {
        if (! $this->AdminTable)
        {
            $this->AdminTable = new AdminGateway($this->adapter);
        }
        return $this->AdminTable;
    }protected function getAdminCategoryTable()
    {
        if (! $this->AdminCategoryTable)
        {
            $this->AdminCategoryTable = new AdminCategoryGateway($this->adapter);
        }
        return $this->AdminCategoryTable;
    }protected function getAdsTable()
    {
        if (! $this->AdsTable)
        {
            $this->AdsTable = new AdsGateway($this->adapter);
        }
        return $this->AdsTable;
    }protected function getAlbumTable()
    {
        if (! $this->AlbumTable)
        {
            $this->AlbumTable = new AlbumGateway($this->adapter);
        }
        return $this->AlbumTable;
    }protected function getArticleTable()
    {
        if (! $this->ArticleTable)
        {
            $this->ArticleTable = new ArticleGateway($this->adapter);
        }
        return $this->ArticleTable;
    }protected function getAudioTable()
    {
        if (! $this->AudioTable)
        {
            $this->AudioTable = new AudioGateway($this->adapter);
        }
        return $this->AudioTable;
    }protected function getBuyLogTable()
    {
        if (! $this->BuyLogTable)
        {
            $this->BuyLogTable = new BuyLogGateway($this->adapter);
        }
        return $this->BuyLogTable;
    }protected function getCategoryTable()
    {
        if (! $this->CategoryTable)
        {
            $this->CategoryTable = new CategoryGateway($this->adapter);
        }
        return $this->CategoryTable;
    }protected function getCommentTable()
    {
        if (! $this->CommentTable)
        {
            $this->CommentTable = new CommentGateway($this->adapter);
        }
        return $this->CommentTable;
    }protected function getDeviceUserTable()
    {
        if (! $this->DeviceUserTable)
        {
            $this->DeviceUserTable = new DeviceUserGateway($this->adapter);
        }
        return $this->DeviceUserTable;
    }protected function getDownloadTable()
    {
        if (! $this->DownloadTable)
        {
            $this->DownloadTable = new DownloadGateway($this->adapter);
        }
        return $this->DownloadTable;
    }protected function getFavoriteTable()
    {
        if (! $this->FavoriteTable)
        {
            $this->FavoriteTable = new FavoriteGateway($this->adapter);
        }
        return $this->FavoriteTable;
    }protected function getFinancialTable()
    {
        if (! $this->FinancialTable)
        {
            $this->FinancialTable = new FinancialGateway($this->adapter);
        }
        return $this->FinancialTable;
    }protected function getFocusRelationTable()
    {
        if (! $this->FocusRelationTable)
        {
            $this->FocusRelationTable = new FocusRelationGateway($this->adapter);
        }
        return $this->FocusRelationTable;
    }protected function getImageTable()
    {
        if (! $this->ImageTable)
        {
            $this->ImageTable = new ImageGateway($this->adapter);
        }
        return $this->ImageTable;
    }protected function getLabelTable()
    {
        if (! $this->LabelTable)
        {
            $this->LabelTable = new LabelGateway($this->adapter);
        }
        return $this->LabelTable;
    }protected function getLoginTable()
    {
        if (! $this->LoginTable)
        {
            $this->LoginTable = new LoginGateway($this->adapter);
        }
        return $this->LoginTable;
    }protected function getMicroblogTable()
    {
        if (! $this->MicroblogTable)
        {
            $this->MicroblogTable = new MicroblogGateway($this->adapter);
        }
        return $this->MicroblogTable;
    }protected function getMobileAppealTable()
    {
        if (! $this->MobileAppealTable)
        {
            $this->MobileAppealTable = new MobileAppealGateway($this->adapter);
        }
        return $this->MobileAppealTable;
    }protected function getModuleTable()
    {
        if (! $this->ModuleTable)
        {
            $this->ModuleTable = new ModuleGateway($this->adapter);
        }
        return $this->ModuleTable;
    }protected function getNavigationTable()
    {
        if (! $this->NavigationTable)
        {
            $this->NavigationTable = new NavigationGateway($this->adapter);
        }
        return $this->NavigationTable;
    }protected function getNotificationTable()
    {
        if (! $this->NotificationTable)
        {
            $this->NotificationTable = new NotificationGateway($this->adapter);
        }
        return $this->NotificationTable;
    }protected function getNotificationRecordsTable()
    {
        if (! $this->NotificationRecordsTable)
        {
            $this->NotificationRecordsTable = new NotificationRecordsGateway($this->adapter);
        }
        return $this->NotificationRecordsTable;
    }protected function getPraiseTable()
    {
        if (! $this->PraiseTable)
        {
            $this->PraiseTable = new PraiseGateway($this->adapter);
        }
        return $this->PraiseTable;
    }protected function getRegionTable()
    {
        if (! $this->RegionTable)
        {
            $this->RegionTable = new RegionGateway($this->adapter);
        }
        return $this->RegionTable;
    }protected function getReportTable()
    {
        if (! $this->ReportTable)
        {
            $this->ReportTable = new ReportGateway($this->adapter);
        }
        return $this->ReportTable;
    }protected function getScreenTable()
    {
        if (! $this->ScreenTable)
        {
            $this->ScreenTable = new ScreenGateway($this->adapter);
        }
        return $this->ScreenTable;
    }protected function getSetupTable()
    {
        if (! $this->SetupTable)
        {
            $this->SetupTable = new SetupGateway($this->adapter);
        }
        return $this->SetupTable;
    }protected function getSmsCodeTable()
    {
        if (! $this->SmsCodeTable)
        {
            $this->SmsCodeTable = new SmsCodeGateway($this->adapter);
        }
        return $this->SmsCodeTable;
    }protected function getUserTable()
    {
        if (! $this->UserTable)
        {
            $this->UserTable = new UserGateway($this->adapter);
        }
        return $this->UserTable;
    }protected function getUserPartnerTable()
    {
        if (! $this->UserPartnerTable)
        {
            $this->UserPartnerTable = new UserPartnerGateway($this->adapter);
        }
        return $this->UserPartnerTable;
    }protected function getWatchRecordTable()
    {
        if (! $this->WatchRecordTable)
        {
            $this->WatchRecordTable = new WatchRecordGateway($this->adapter);
        }
        return $this->WatchRecordTable;
    }
    protected function getViewAdminTable()
    {
        if (! $this->ViewAdminTable)
        {
            $this->ViewAdminTable = new ViewAdminGateway($this->adapter);
        }
        return $this->ViewAdminTable;
    }
    protected function getViewUserTable()
    {
        if (! $this->ViewUserTable)
        {
            $this->ViewUserTable = new ViewUserGateway($this->adapter);
        }
        return $this->ViewUserTable;
    }
    protected function getViewAdsTable()
    {
        if (! $this->ViewAdsTable)
        {
            $this->ViewAdsTable = new ViewAdsGateway($this->adapter);
        }
        return $this->ViewAdsTable;
    }
    protected function getViewNavigationTable()
    {
        if (! $this->ViewNavigationTable)
        {
            $this->ViewNavigationTable = new ViewNavigationGateway($this->adapter);
        }
        return $this->ViewNavigationTable;
    }
    protected function getViewMobileAppealTable()
    {
        if (! $this->ViewMobileAppealTable)
        {
            $this->ViewMobileAppealTable = new ViewMobileAppealGateway($this->adapter);
        }
        return $this->ViewMobileAppealTable;
    }
    protected function getViewAudioTable()
    {
        if (! $this->ViewAudioTable)
        {
            $this->ViewAudioTable = new ViewAudioGateway($this->adapter);
        }
        return $this->ViewAudioTable;
    }

    protected function getViewCategoryTable()
    {
        if (! $this->ViewCategoryTable)
        {
            $this->ViewCategoryTable = new ViewCategoryGateway($this->adapter);
        }
        return $this->ViewCategoryTable;
    }
    protected function getViewMicroblogTable()
    {
        if (! $this->ViewMicroblogTable)
        {
            $this->ViewMicroblogTable = new ViewMicroblogGateway($this->adapter);
        }
        return $this->ViewMicroblogTable;
    }
    protected function getViewAlbumTable()
    {
        if (! $this->ViewAlbumTable)
        {
            $this->ViewAlbumTable = new ViewAlbumGateway($this->adapter);
        }
        return $this->ViewAlbumTable;
    }
    protected function getViewArticleTable()
    {
        if (! $this->ViewArticleTable)
        {
            $this->ViewArticleTable = new ViewArticleGateway($this->adapter);
        }
        return $this->ViewArticleTable;
    }
    protected function getViewCommentTable()
    {
        if (! $this->ViewCommentTable)
        {
            $this->ViewCommentTable = new ViewCommentGateway($this->adapter);
        }
        return $this->ViewCommentTable;
    }
    protected function getViewFinancialTable()
    {
        if (! $this->ViewFinancialTable)
        {
            $this->ViewFinancialTable = new ViewFinancialGateway($this->adapter);
        }
        return $this->ViewFinancialTable;
    }
    protected function getViewFavoriteTable()
    {
        if (! $this->ViewFavoriteTable)
        {
            $this->ViewFavoriteTable = new ViewFavoriteGateway($this->adapter);
        }
        return $this->ViewFavoriteTable;
    }
    protected function getViewWatchRecordTable()
    {
        if (! $this->ViewWatchRecordTable)
        {
            $this->ViewWatchRecordTable = new ViewWatchRecordGateway($this->adapter);
        }
        return $this->ViewWatchRecordTable;
    }
    protected function getViewFocusRelationTable()
    {
        if (! $this->ViewFocusRelationTable)
        {
            $this->ViewFocusRelationTable = new ViewFocusRelationGateway($this->adapter);
        }
        return $this->ViewFocusRelationTable;
    }
    protected function getViewReportTable()
    {
        if (! $this->ViewReportTable)
        {
            $this->ViewReportTable = new ViewReportGateway($this->adapter);
        }
        return $this->ViewReportTable;
    }
}
