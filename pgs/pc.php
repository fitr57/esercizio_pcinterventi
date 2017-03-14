<?php 
	$msg='';
	$id = (!empty($_REQUEST['id'])) ? intval($_REQUEST['id']) : false;
	$intervento=(empty($_REQUEST['id'])) ?  R::dispense('pc') : R::load('pc', intval($_REQUEST['id']));
	//$estensione=(empty($_REQUEST['id'])) ?  R::dispense('interventi') : R::load('interventi', intval($_REQUEST['id']));
	//$estense=(empty($_REQUEST['id'])) ?  R::dispense('listainterventi') : R::load('listainterventi', intval($_REQUEST['id']));
	
	//STESSA COSA QUA SOTTO
	if (!empty($_REQUEST['hostname'])) : 
		$intervento->hostname=$_POST['hostname'];
		$intervento->marche_id=$_POST['marche_id'];
		$intervento->modello=($_POST['modello']);
		$intervento->sn=($_POST['sn']);
		//$estense->descrizone=($_POST['descrizione']);
		//$estense->dataintervento=date_create($_POST['dataintervento']);
		
		try {
			R::store($intervento);
			//$msg='Dati salvati correttamente ('.json_encode($estense).') ';
		} catch (RedBeanPHP\RedException\SQL $e) {
			$msg=$e->getMessage();
		}
	endif;	
	
	if (!empty($_REQUEST['del'])) : 
		$intervento=R::load('pc', intval($_REQUEST['del']));
		try{
			R::trash($intervento);
		} catch (RedBeanPHP\RedException\SQL $e) {
			$msg=$e->getMessage();
		}
	endif;
	
	$interventi=R::findAll('pc', 'ORDER by id ASC LIMIT 999');
	$pc=R::findAll('marche');
	$new=!empty($_REQUEST['create']);
	$coll=R::findAll('pc', 'ORDER by id ASC LIMIT 999');
	
	
	
	/*mette nella variabile $var_1 tutto il contenuto della tabella interventi relativa a quel pc che glel'ho passato tramite il $id
	  da notare che nella query che vado ad usare per mettere il relativo id devo mettere .$id e questo è molto molto importante*/
	 
	 
	$var_1  = ($id) ? R::find( 'interventi', ' pc_id='.$id) : Array();
	
	
	//FUNZIONI PER SOMMA DI ORE E SPESA
	$totspesa=R::getCell('select SUM(spesa) from interventi');
	$totore=R::getCell('select SUM(ore) from interventi');
?>

<h1>
	<a href="index.php">
		<?=($id) ? ($new) ? 'Nuovo PC' : 'PC n. '.$id : 'PC';?>
	</a>
</h1>
<?php if ($id || $new) : ?>


		<form method="post" action="?p=pc">
			<?php if ($id) : ?>
				<input type="hidden" name="id" value="<?=$intervento->id?>" />
			<?php endif; ?>
			<label for="marche_id">
				Marche
			</label>
			<select name="marche_id">
				<option />
				<?php foreach ($pc as $a) : ?>
					<option value="<?=$a->id?>" <?=($a->id==$id) ? 'selected' :'' ?> >
						<?=$a->marca?>
					</option>
				<?php endforeach; ?>
			</select>
			<label for="hostname">
				Cliente
			</label>
			<input name="hostname"  value="<?=$intervento->hostname?>" autofocus required  />
			<label for="modello">
				Modello
			</label>
			<input name="modello"  value="<?=$intervento->modello?>"/>
			
			<label for="sn">
				Seriale
			</label>			
			<input name="sn"  value="<?=$intervento->sn?>"  />	
			
			
	
			
			
			

			<!--stampa a video tutto il contenuto dell'array-->
			<//?= json_encode($var_1) ?>
			
			<h3>Interventi</h3>
			<ul>
			<?php foreach ($var_1 as $ntrv): ?>
				<li><?=$ntrv->descrizione?></li>
			<?php endforeach; ?>
			</ul>
			
			
			
			
			
			
			<button type="submit" tabindex="-1">
				Salva
			</button>
			
			<a href="?p=pc" >
				Elenco
			</a>			
			
			<a href="?p=pc&del=<?=$intervento['id']?>" tabindex="-1">
				Elimina
			</a>					
		</form>
<?php else : ?>
<!-- PULSANTE NECESSARIO PER FAR FUNZIONARE DATA RANGE PICKER (controllare l'id) -->
<!--button class="btn btn-info" id="intervallo">Filtro Date</button--> 

	<div class="tablecontainer">
		<table id="ema" class="table responsive table-bordered table-striped" style="table-layout:fixed">
			<colgroup>
				<col style="width:150px" />
			</colgroup>
			<thead>
				<tr>
					<th>Marche</th>
					<th>nome</th>
					<th>modello</th>
					<th>seriale</th>
				
					
					<th style="width:100px;text-align:center">Modifica</th>
					<th style="width:100px;text-align:center">Cancella</th>
				</tr>
			</thead>
			
			<!--tfoot>
                <tr>
                    <th colspan="4" style="text-align:right">Total:</th>
                    <th></th>
                </tr>
            </tfoot-->
			
			<tbody>
			
			<?php foreach ($coll as $r) : ?>
				<tr>
					<td>
							<?=($r->marche_id) ? $r->marche->marca : ''?>
					</td>			
					<td>
						<?=$r->hostname ?>
					</td>
					<td style="text-align:right" >
						<?=$r->modello?>
					</td>	
					<td style="text-align:right" >
						<?=$r->sn?>
					</td>
		
					<td style="text-align:center" >
						<a href="?p=pc&id=<?=$r['id']?>">
							Mod.
						</a>
					</td>
					<td style="text-align:center" >
						<a href="?p=pc&del=<?=$r['id']?>" tabindex="-1">
							x
						</a>
					</td>							
				</tr>		
			<?php endforeach; ?>
			</tbody>
		</table>
		
		<label>Il totale ore è:</label>
		<h1> <?php echo $totore;  ?></h1>
		</br>
		<label>Il totale spesa:</label>
		<h1> <?php echo $totspesa;  ?></h1>
		
		
		<h4 class="msg">
			<?=$msg?>
		</h4>	
	</div>
<?php endif; ?>
<a href="?p=pc&create=1">Inserisci nuovo</a>
<script src="https://code.jquery.com/jquery-3.1.1.js"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
<script>
	var chg=function(e){
		console.log(e.name,e.value)
		document.forms.frm.elements[e.name].value=(e.value) ? e.value : null
	}	
$(document).ready(function() {
//DATATABLE
//metto alla variabile otable la mia tabella che ho creato
$('#ema').dataTable()});


/*
//DATE RANGE
var startdate;
var enddate;
//prendo il mio input e ci metto il datepicker
$('#intervallo').daterangepicker({
format: 'DD/MM/YYYY',
  autoUpdateInput: false,
      locale: {
          cancelLabel: 'Clear'
      }
},
function(start, end,label) {
//grazie alla libreria moment converto in stringa le due date fornite con il date picker
var s = moment(start.toISOString());
var e = moment(end.toISOString());
startdate = s.format("YYYY-MM-DD");
enddate = e.format("YYYY-MM-DD");
});
//creazione del filtro con i vari id
$('#intervallo').on('apply.daterangepicker', function(ev, picker) {
startdate=picker.startDate.format('YYYY-MM-DD');
enddate=picker.endDate.format('YYYY-MM-DD');
oTable.fnDraw();
});
$.fn.dataTableExt.afnFiltering.push(
function( oSettings, aData, iDataIndex ) {
if(startdate!=undefined){
//indice della colonna dove si trovano le date nel mio caso 1
//e conversione successiva nel formato dd-mm-yyyy

var coldate = aData[1].split("/");
var d = new Date(coldate[2], coldate[1]-1 , coldate[0]);
var date = moment(d.toISOString());
date =    date.format("YYYY-MM-DD");

dateMin=startdate.replace(/-/g, "");
dateMax=enddate.replace(/-/g, "");
date=date.replace(/-/g, "");
//console.log(dateMin, dateMax, date);
if ( dateMin == "" && date <= dateMax){
return true;
}
else if ( dateMin =="" && date <= dateMax ){
return true;
}
else if ( dateMin <= date && "" == dateMax ){
return true;
}
else if ( dateMin <= date && date <= dateMax ){
return true;
}
return false;
}
}
);
} );*/
</script>