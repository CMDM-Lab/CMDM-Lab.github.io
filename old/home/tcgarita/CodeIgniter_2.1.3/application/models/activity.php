<?
class Activity extends CI_Model {
    function __construct()
    {
	parent::__construct();
    }

    function get_activities() {
	return array(
	    array('Collaborative computational drug discovery for neglected diseases','Steering Committee/Scientific Board Member','2010'),
	    array('Division of Computers in Chemistry, American Chemistry Society National Meetings','Programming Board','2009'),
	    array('Division of Computers in Chemistry, American Chemistry Society National Meetings','Chair for Drug Discovery Symposium',2009),
	    array('Division of Computers in Chemistry, American Chemistry Society National Meetings','Media & Member Relations Chair',2009),
	    array('International society of metabolomics','Member',2007),
	    array('American Chemical Society','Member',1999),
	    array('The Journal of Combinatorial Chemistry and High Throughput Screening / Special Issue','Guest-Editor-in-Chief',2013),
	    array('The Journal of Molecular Graphics and Modelling','Editorial Advisor',2011),
	    array('The Journal of Combinatorial Chemistry and High Throughput Screening','Editorial Advisor',2010),
	    array('Collaborative computational drug discovery for neglected diseases. Steering Committee','Scientific Board Member',2010),
	    array('Agency for Science, Technology and Research (A*STAR), Singapore','Ad hoc Grant Reviewer',2010),
	    array('Computers in Chemistry Division, American Chemistry Society','Media & Member Relations Chair',1999), 
	    array('Computers in Chemistry Division, American Chemistry Society','Programming Board Member',2009),
	    array('Division of Computers in Chemistry, American Chemistry Society National','Chair for Drug Discovery Symposium',2009)
	    );
    }
    function get_reviewer(){
	return array(
	    array('The Korean Journal of Chemical Engineering','Reviewer',2012),
	    array('Toxicological Sciences','Reviewer',2007),
	    array('Journal of Chemical Information and Modeling','Reviewer',2006),
	    array('Journal of the Biomedical Sciences','Reviewer',2006),
	    array('Journal of the Formosan Medical Association','Reviewer',2006),
	    array('Journal of the Taiwan Institute of Chemical Engineers','Reviewer',2006),
	    array('Biomedical Engineering: Applications, Basis and Communications','Reviewer',2006),
	    array('Journal of Chemical Information and Computer Science','Reviewer',2002)
	); 
    }
    function get_talks(){
	return array(
	    array('Metabolomics in sepsis marker discovery','November 2012'),
	    array('LeadOp: Structure-based fragment hopping for lead optimization using pre-docked fragment database, (Invited talk, Drug Discovery Symposium)',		'August 2012'),
	    array('Histone Methyltransferase G9a Inhibitor抑制劑的發展','June 2012'),
	    array('藥物開發-從樹皮到計算機','June 2012'),
	    array('Metabolomics in Exhaled Breath Condensate', 'June 2012'),
	    array('Metabolomics in ECMO and surgical research','December 2011'),
	    array('Cancer Metabolism and Cancer Metabolomics', 'November 2011'),
	    array('Introduction of metabolomics', 'November 2011'),
	    array('NTU Metabolomics Core Laboratory','October 2011'),
	    array('Cheminformatics Aspects of High Throughput Screening: From Robots to Models', 'August 2011'),
	    array('Translational Cancer Metabolomics Research in NTU', 'July 2011'),
	    array('3Omics: a web based systems biology visualization tool for integrating human transcriptomic, proteomic and metabolomic data(Invited talk, Systems Biology Section)','June 2011'),
	    array('Structure Hunter: Prediction of novel chemical structures in a mixture (Invited talk, Technology Innovation Section)','June 2011'),
	    array('Computer-aided drug discovery - what can modelers help!', 'May 2011'),
	    array('In Silico Lead Optimization & Preclinical Safety Screening','Janunary 2011'),
	    array('NTU Metabolomics Core Laboratory', 'November 2010'),
	    array('Metabolomics in Translational Medicine - National Taiwan University Metabolomics Core', 'October 2010'),
	    array('Agricultural Biotechnology Research Center (ABRC) at Academia Sinica Computer-aided Molecular Design and Metabolomics10/29/2010', 'October 2010'),
	    array('Drug Discovery From Tree To Computer', 'October 2010'),
	    array('Nutritional Metabonomics in Translational Medicine', 'September 2010'),
	    array('Computational hERG Toxicity Classiﬁcation Model Based on QSAR and Support Vector Machines (Invited talk, Skolnik Award Symposium)','August 2010'),
	    array('Drug Discovery: What Can Modelers Help?','July 2010'),
	    array('Metabonomics', 'June 2010'),
	    array('In Silico Lead Optimization & Preclinical Safety Screening 2', 'July 2010'),
	    array('Discovery and Development of VEGFR3 inhibitors as the Targeted Cancer Therapies', 'June 2010'),
	    array('Drug Discovery: What Can Modelers Help?', 'May 2010'),
	    array('Metabonomics', 'May 2010'),
	    array('Metabolomics in Translational Medicine', 'March 2010'),
	    array('Metabonomics in Toxicity', 'Janunary 2010'),
	    array('An introduction to metabolomics and its application to environmental health and occupational medicine', 'Janunary 2010'),
	    array('Metabolomics in Translational Medicine', 'September 2009'),
	    array('BBB Permeable MRI Contrast Agent: are we there yet?','November 2008') 
	);    
    }       
}
?>
