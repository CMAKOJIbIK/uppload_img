<?php
if (isset($_FILES['image'])) {
    $fileTmpName = $_FILES['image']['tmp_name'];
    $errorCode = $_FILES['image']['error'];
    if ($errorCode !== UPLOAD_ERR_OK || !is_uploaded_file($fileTmpName)) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE   => 'Размер файла превысил значение upload_max_filesize в конфигурации PHP.',
            UPLOAD_ERR_FORM_SIZE  => 'Размер загружаемого файла превысил значение MAX_FILE_SIZE в HTML-форме.',
            UPLOAD_ERR_PARTIAL    => 'Загружаемый файл был получен только частично.',
            UPLOAD_ERR_NO_FILE    => 'Файл не был загружен.',
            UPLOAD_ERR_NO_TMP_DIR => 'Отсутствует временная папка.',
            UPLOAD_ERR_CANT_WRITE => 'Не удалось записать файл на диск.',
            UPLOAD_ERR_EXTENSION  => 'PHP-расширение остановило загрузку файла.',
        ];
        $unknownMessage = 'При загрузке файла произошла неизвестная ошибка.';
        $outputMessage = isset($errorMessages[$errorCode]) ? $errorMessages[$errorCode] : $unknownMessage;
        die($outputMessage);
    } else {
        $fi = finfo_open(FILEINFO_MIME_TYPE);
        $mime = (string) finfo_file($fi, $fileTmpName);
        if (strpos($mime, 'image') === false) die('Можно загружать только изображения.');

        $image = getimagesize($fileTmpName);

        $limitBytes  = 1024 * 1024 * 5;
        $limitWidth  = 1280;
        $limitHeight = 768;

        if (filesize($fileTmpName) > $limitBytes) die('Размер изображения не должен превышать 5 Мбайт.');
        if ($image[1] > $limitHeight)             die('Высота изображения не должна превышать 768 точек.');
        if ($image[0] > $limitWidth)              die('Ширина изображения не должна превышать 1280 точек.');

        $name = getRandomFileName($fileTmpName);

        $extension = image_type_to_extension($image[2]);

        $format = str_replace('jpeg', 'jpg', $extension);

        if (!move_uploaded_file($fileTmpName, __DIR__ . '/upload/' . $name . $format)) {
            die('При записи изображения на диск произошла ошибка.');
        }

        echo 'Картинка успешно загружена!';
    }
};

function getRandomFileName($path)
{
    $path = $path ? $path . '/' : '';
    do {
        $name = md5(microtime() . rand(0, 9999));
        $file = $path . $name;
    } while (file_exists($file));

    return $name;
}