<?php
namespace Utm;

/**
 * Class that handle the framework response.
 * The renderer is composed by 3 part :
 * Header, Body and Footer.
 */
class CoreResponse
{
    /**
     * Array containing header information to be rendered with php header 
     * function.
     * @var array 
     */
    protected $m_aHeader = [];
    
    /**
     * This var handle the body response.
     * @var string
     */
    protected $m_sBody;
    
    /**
     * Footer storage.
     * @var string 
     */
    protected $m_sFooter;
    
    /**
     * Check if can send header.
     * @return boolean
     */
    public function canSetHeader()
    {
        return !headers_sent();
    }
    
    /**
     * Add header line to the array in order to be rendered at the end.
     * @param string $p_sHeaderLine
     * @throws \Exception
     */
    public function setHeader($p_sHeaderLine)
    {
        if (true == $this->canSetHeader()) {
            $this->m_aHeader[] = $p_sHeaderLine;
        } else {
            throw new \Exception("header already sent.");
        }
    }
    
    /**
     * Clean all header info.
     * @throws \Exception
     */
    public function resetHeader()
    {
        if (true == $this->canSetHeader()) {
            $this->m_aHeader = [];
            return $this;
        } else {
            throw new \Exception("header already sent.");
        }
    }
    
    /**
     * Render each header line.
     */
    public function renderHeader()
    {
        if (true == $this->canSetHeader() && true == count($this->m_aHeader)) {
            foreach ($this->m_aHeader as $headerLine) {
                header($headerLine);
            }
        }
    }
    
    /**
     * Set the response body.
     * @param string $p_sContent
     */
    public function setBody($p_sContent)
    {
        $this->m_sBody = $p_sContent;
        return $this;
    }
    
    /**
     * Add content to the response body at the end.
     * @param string $p_sContent
     */
    public function appendBody($p_sContent)
    {
        $this->m_sBody .= $p_sContent;
        return $this;
    }
    
    /**
     * Add content to the response body at the start.
     * @param string $p_sContent
     */
    public function prependBody($p_sContent)
    {
        $this->m_sBody = $p_sContent . $this->m_sBody;
        return $this;
    }
    
    /**
     * Access the body value.
     * @param string $p_sContent
     */
    public function getBody()
    {
        return $this->m_sBody;
    }
    
    /**
     * Render the body if not empty.
     */
    public function renderBody()
    {
        if (strlen($this->m_sBody)) {
            echo $this->m_sBody;
        }
    }
    
    /**
     * Set the response footer.
     * @param string $p_sContent
     */
    public function setFooter($p_sContent)
    {
        $this->m_sFooter = $p_sContent;
        return $this;
    }
    
    /**
     * Access the footer value.
     * @param string $p_sContent
     */
    public function getFooter()
    {
        return $this->m_sFooter;
    }
    
    /**
     * Render the footer if not empty.
     */
    public function renderFooter()
    {
        if (strlen($this->m_sFooter)) {
            echo $this->m_sFooter;
        }
    }
    
    /**
     * Render every element of the response.
     */
    public function render()
    {
        $this->renderHeader();
        $this->renderBody();
        $this->renderFooter();
    }
}
