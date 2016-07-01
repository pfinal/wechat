<?php

namespace PFinal\Wechat;

/**
 * 类自动加载器
 * @author  Zou Yiliang
 * @since   1.0
 */
class ClassLoader
{
    private $directory;
    private $prefix;
    private $prefixLength;

    /**
     * @param string $baseDirectory
     */
    public function __construct($baseDirectory = __DIR__)
    {
        $this->directory = $baseDirectory;
        $this->prefix = __NAMESPACE__ . '\\';
        $this->prefixLength = strlen($this->prefix);
    }

    /**
     * 注册自动加载器到 PHP SPL autoloader
     *
     * @param bool $prepend
     */
    public static function register($prepend = false)
    {
        spl_autoload_register(array(new self(), 'autoload'), true, $prepend);
    }

    /**
     * @param string $className 完整类名
     */
    public function autoload($className)
    {
        if (0 === strpos($className, $this->prefix)) {
            $parts = explode('\\', substr($className, $this->prefixLength));
            $filePath = $this->directory . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts) . '.php';

            if (is_file($filePath)) {
                require $filePath;
            }
        }
    }
}
