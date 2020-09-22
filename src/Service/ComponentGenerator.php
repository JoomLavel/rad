<?php
/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 14.09.2020
 * Time: 20:11
 */

namespace JoomLavel\Rad\Service;


class ComponentGenerator
{
    protected $generalPath;
    protected $templateDirectory;
    protected $workplaceDirectory;
    protected $defaultTemplateName;

    private $componentDirectory;
    private $customNameInLowerCase;
    private $customNameInCamelCase;
    private $customName;

    private $templatePath;
    private $separator = '\\';
    private $defaultTemplatePath;
    private $publishPath;

    private $cleanExec;

    const COMP_XML = 'joomlavelcnct.xml';
    const COMP_PHP = 'joomlavelcnct.php';
    const COMP_DIR = 'joomlavelcnct';
    const COMP_CAMEL_NAME = 'JoomLavelCNCT';


    /**
     * ComponentGenerator constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->generalPath = $config['general']['path'];
        $this->templateDirectory = $config['rad']['templates']['path'];
        $this->workplaceDirectory = $config['rad']['workplace']['path'];
        $this->defaultTemplateName = $config['rad']['templates']['default'];
        $this->publishPath = $config['rad']['publish']['path'];

        $this->templatePath = $this->generalPath . $this->separator . '..' . $this->separator . $this->templateDirectory;
        $this->defaultTemplatePath = $this->templatePath . $this->separator . $this->defaultTemplateName;

        $this->cleanExec = $config['rad']['workplace']['cleanExec'];

        if (!file_exists($this->defaultTemplatePath)) {
            echo "Template does not exist!";
        }
    }

    /**
     * @param string $sourcePath
     * @param string $destinationPath
     */
    private static function copyDir(string $sourcePath, string $destinationPath): void
    {
        $dir = opendir($sourcePath);
        @mkdir($destinationPath);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($sourcePath . '/' . $file)) {
                    static::copyDir($sourcePath . '/' . $file, $destinationPath . '/' . $file);
                } else {
                    copy($sourcePath . '/' . $file, $destinationPath . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function setComponentName(string $name)
    {
        $this->customNameInLowerCase = strtolower($name);
        $this->customNameInCamelCase = ucwords($name);
        $this->customName = $name;

        return true;
    }

    /**
     * @param bool $force
     * @return string
     */
    public function checkExistingFilesAndDirectories(bool $force)
    {
        $note = '';
        $date = date('yymd-Hms');
        if (file_exists($this->publishPath . $this->separator . $this->customName . '.zip')) {
            if ($force == true) {
                rename($this->publishPath . $this->separator . $this->customName . '.zip',
                    $this->publishPath . $this->separator . $this->customName .
                    $date . '.zip.bak');
            } else {
                $note = 'Zip File with given name: ' . $this->customName . '.zip already exist!';
            }
        }

        if (file_exists($this->generalPath . $this->separator .
            '..' . $this->separator . $this->workplaceDirectory .
            $this->separator . $this->customNameInLowerCase)) {

            if ($force == true) {
                rename($this->generalPath . $this->separator .
                    '..' . $this->separator . $this->workplaceDirectory .
                    $this->separator . $this->customNameInLowerCase,
                    $this->generalPath . $this->separator .
                    '..' . $this->separator . $this->workplaceDirectory .
                    $this->separator . $this->customNameInLowerCase . $date . '.bak');
            } else {
                $note = 'directory ' . $this->customNameInLowerCase . ' for work already exist!';
            }
        }
        return $note;
    }

    /**
     * @return string
     */
    public function copyTemplateToDirectory()
    {

        $dirTo = $this->generalPath . $this->separator . '..' . $this->separator . $this->workplaceDirectory;
        $this->componentDirectory = $dirTo . $this->separator . $this->customNameInLowerCase;

        $this->copyDir($this->defaultTemplatePath, $this->componentDirectory);

        return true;
    }

    /**
     * @return bool
     */
    public function renameFilesAndDirectories()
    {
        rename($this->componentDirectory . $this->separator . $this::COMP_XML,
            $this->componentDirectory . $this->separator . $this->customNameInLowerCase . '.xml');

        rename($this->componentDirectory . $this->separator . 'admin' . $this->separator . $this::COMP_PHP,
            $this->componentDirectory . $this->separator . 'admin' . $this->separator . $this->customNameInLowerCase . '.php');

        rename($this->componentDirectory . $this->separator . 'site' . $this->separator . $this::COMP_PHP,
            $this->componentDirectory . $this->separator . 'site' . $this->separator . $this->customNameInLowerCase . '.php');

        rename($this->componentDirectory . $this->separator . 'site' . $this->separator . 'models' . $this->separator . $this::COMP_PHP,
            $this->componentDirectory . $this->separator . 'site' . $this->separator . 'models' . $this->separator . $this->customNameInLowerCase . '.php');

        rename($this->componentDirectory . $this->separator . 'site' . $this->separator . 'views' . $this->separator . $this::COMP_DIR,
            $this->componentDirectory . $this->separator . 'site' . $this->separator . 'views' . $this->separator . $this->customNameInLowerCase);

        return true;
    }

    /**
     * @return mixed
     */
    public function manipulateXml()
    {
        $xml = simplexml_load_file($this->componentDirectory . $this->separator . $this->customNameInLowerCase . '.xml');

        $xml->name = $this->customName;
        $xml->description = $this->customName . "(description add here)";

        $xml->files->filename[1] = $this->customNameInLowerCase . '.php';

        $xml->administration->files->filename[1] = $this->customNameInLowerCase . '.php';

        $xml->administration->menu = $this->customName;
        $xml->administration->menu['link'] = 'index.php?option=com_' . $this->customNameInLowerCase;

        $xml->saveXML($this->componentDirectory . $this->separator . $this->customNameInLowerCase . '.xml');

        return true;
    }

    /**
     * @return mixed
     */
    public function manipulatePhp()
    {
        $model = $this->componentDirectory . $this->separator . 'site' . $this->separator .
            'models' . $this->separator . $this->customNameInLowerCase . '.php';

        $view = $this->componentDirectory . $this->separator . 'site' . $this->separator .
            'views' . $this->separator . $this->customNameInLowerCase . $this->separator . 'view.html.php';

        $controller = $this->componentDirectory . $this->separator . 'site' . $this->separator . 'controller.php';
        $routing = $this->componentDirectory . $this->separator . 'site' . $this->separator . $this->customNameInLowerCase . '.php';

        file_put_contents($model, str_replace($this::COMP_CAMEL_NAME, $this->customNameInCamelCase, file_get_contents($model)));
        file_put_contents($view, str_replace($this::COMP_CAMEL_NAME, $this->customNameInCamelCase, file_get_contents($view)));
        file_put_contents($controller, str_replace($this::COMP_CAMEL_NAME, $this->customNameInCamelCase, file_get_contents($controller)));
        file_put_contents($routing, str_replace($this::COMP_CAMEL_NAME, $this->customNameInCamelCase, file_get_contents($routing)));

        return true;
    }


    /**
     * @return mixed
     */
    public function cleanDirectory()
    {
        !$this->cleanExec ?: exec($this->cleanExec);

        return true;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function moveZipFile(string $name)
    {
        rename($name . '.zip', $this->publishPath . "/" . $name . '.zip');

        return true;
    }

    /**
     * @return mixed
     */
    public function getComponentDirectory()
    {
        return $this->componentDirectory;
    }


}