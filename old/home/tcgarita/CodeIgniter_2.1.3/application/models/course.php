<?
class Course extends CI_Model {
    function __construct()
    {
	parent::__construct();
    }

    function get_courses() {
	return array(
	    array('101-2','生物資訊導論','Introduction to Bioinformatics','研究所暨大學部高年級'),
	    array('101-2','生物資訊學','Bioinformatics','研究所'),
	    array('101-2','系統代謝學','Systems Analysis on Metabolomics','研究所'),
	    array('101-1','高等電腦模擬藥物設計','Advanced Computer-aided Drug Design','研究所暨大學部高年級'),
	    array('101-1','新藥探索一','New Drug Discovery (I)','藥學所'),
	    array('100-2','代謝體學','Metabolomics','研究所'),
	    array('100-1','化學資訊','Cheminformatics', "能源科技學程、生醫電資所、資工所")
	    );
    }
}
?>
