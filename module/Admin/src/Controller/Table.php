<?php
namespace Admin\Controller\Controller;
        
use Zend\Db\Adapter\Adapter;
use Zend\Mvc\Controller\AbstractActionController;
use Admin\Controller\AdminGateway;use Admin\Controller\AdminCategoryGateway;use Admin\Controller\AdsGateway;use Admin\Controller\AlbumGateway;use Admin\Controller\ArticleGateway;use Admin\Controller\AudioGateway;use Admin\Controller\BuyLogGateway;use Admin\Controller\CategoryGateway;use Admin\Controller\CommentGateway;use Admin\Controller\DeviceUserGateway;use Admin\Controller\DownloadGateway;use Admin\Controller\FavoriteGateway;use Admin\Controller\FinancialGateway;use Admin\Controller\FocusRelationGateway;use Admin\Controller\ImageGateway;use Admin\Controller\LabelGateway;use Admin\Controller\LoginGateway;use Admin\Controller\MicroblogGateway;use Admin\Controller\MobileAppealGateway;use Admin\Controller\ModuleGateway;use Admin\Controller\NavigationGateway;use Admin\Controller\NotificationGateway;use Admin\Controller\NotificationRecordsGateway;use Admin\Controller\PraiseGateway;use Admin\Controller\RegionGateway;use Admin\Controller\ReportGateway;use Admin\Controller\ScreenGateway;use Admin\Controller\SetupGateway;use Admin\Controller\SmsCodeGateway;use Admin\Controller\UserGateway;use Admin\Controller\UserPartnerGateway;use Admin\Controller\WatchRecordGateway;
        
class Table extends AbstractActionController
{
        
    public $adapter;
protected $AdminTable;protected $AdminCategoryTable;protected $AdsTable;protected $AlbumTable;protected $ArticleTable;protected $AudioTable;protected $BuyLogTable;protected $CategoryTable;protected $CommentTable;protected $DeviceUserTable;protected $DownloadTable;protected $FavoriteTable;protected $FinancialTable;protected $FocusRelationTable;protected $ImageTable;protected $LabelTable;protected $LoginTable;protected $MicroblogTable;protected $MobileAppealTable;protected $ModuleTable;protected $NavigationTable;protected $NotificationTable;protected $NotificationRecordsTable;protected $PraiseTable;protected $RegionTable;protected $ReportTable;protected $ScreenTable;protected $SetupTable;protected $SmsCodeTable;protected $UserTable;protected $UserPartnerTable;protected $WatchRecordTable;
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
}