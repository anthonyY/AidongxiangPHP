#!/bin/sh
path=(Cache public/uploadfiles public/kindeditor/attached public/push_log public/ueditor/php/upload vendor/Core/System/AiiPush/log vendor/Core/System/QcloudIM/signature/linux-signature64 vendor/Core/System/phpExcel);
mod=775;
user=apache;
group=apache;
for i in ${path[@]}; do
    if [ -f "$i" ]; then
        chmod $mod $i;
        chown $user:$group $i;
    fi
    if [ -d "$i" ]; then
        chmod -R $mod $i;
        chown -R $user:$group $i;
    fi
done;
