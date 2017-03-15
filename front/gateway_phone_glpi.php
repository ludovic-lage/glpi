<?php

include ('../inc/includes.php');

Session::checkLoginUser();

function searchTel($phone,$field,$itemtype)
{
	$tab = array(
			'criteria' => array( 0 =>
                			array(
                        	        'field' => $field, // Champs correspondant au téléphone
                                	'searchtype' => "contains",
	                                'value' => $phone,))
					);
	$params = Search::manageParams($itemtype,$tab);
	$tmp = Search::getDatas($itemtype,$params);
	$id_result = $tmp[data][rows][0][id];
	if(isset($id_result) && $id_result != "") {
		if ($item = getItemForItemtype($itemtype)) {
		       	if ($item->get_item_to_display_tab) {
			       	$item->can($id_result, READ);
					return array ($item->fields["name"],$id_result,$itemtype);
				}
		}
	} else {
		return array ("EMPTY","EMPTY",$itemtype);;
	}
}

if(isset($_GET)){
	if(isset($_GET['telephone'])){
		list ($name_result,$id_result,$item) = searchTel("^".$_GET['telephone']."$","6","User"); // Recherche du numéro de téléphone correspondant à un utilisteur
		if ($name_result == "EMPTY") {
			list ($name_result,$id_result,$item) = searchTel("^".$_GET['telephone']."$","10","User"); // Recherche du numéro de téléphone 2 correspondant à un utilisteur
			if ($name_result == "EMPTY") {
				list ($name_result,$id_result,$item) = searchTel("^".$_GET['telephone']."$","11","User"); // Recherche du numéro de téléphone (mobile) correspondant à un utilisteur
				if ($name_result == "EMPTY") {
					list ($name_result,$id_result,$item) = searchTel("^".$_GET['telephone']."$","5","Entity"); // Recherche du numéro de téléphone correspondant à une entité
				}
			}
		}
		if ($item == "User" && $id_result != "EMPTY") {
			$url= "ticket.form.php?_users_id_requester=$id_result";
                        //header("Location : http://178.32.174.100/glpi/front/ticket.form.php?_users_id_requester=$id_result");
                        echo '<script type="text/javascript">';
                        echo 'window.location.href="'.$url.'";';
                        echo '</script>';

			//header("Location : http://glpi.sig-image.fr/front/ticket.form.php?_users_id_requester=$id_result");
		}
		elseif ($item == "Entity" && $id_result != "EMPTY"){
			header("Location : http://10.10.1.204/front/ticket.form.php?entities_id=$id_result");
		}
		elseif ( $id_result == "EMPTY" ){
			$tel=$_GET['telephone'];
			header("Location : http://10.10.1.204/front/ticket.form.php?name=Inconnu : $tel&content=Appelant : $tel<br>Societe :");
		}
	}
}
Html::footer();

?>
