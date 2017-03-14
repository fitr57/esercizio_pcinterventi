<?php 
	$msg='';
	$id = (!empty($_REQUEST['id'])) ? intval($_REQUEST['id']) : false;
	$intervento=(empty($_REQUEST['id'])) ?  R::dispense('interventi') : R::load('interventi', intval($_REQUEST['id']));
	$dal=(empty($_POST['dal'])) ?  '1970-01-01' : $_POST['dal'];
	$al=(empty($_POST['al'])) ?  date('Y-m-d') : $_POST['al'];
	$al=date('Y-m-d');
	
	if (!empty($_POST['descrizione'])) : 
		$intervento->descrizione=$_POST['descrizione'];
		$intervento->pc_id=$_POST['pc_id'];
		$intervento->spesa=floatval($_POST['spesa']);
		$intervento->ore=intval($_POST['ore']);
		$intervento->dataintervento=date_create($_POST['dataintervento']);
		
		
		try {
			R::store($intervento);
			$msg='Dati salvati correttamente ('.json_encode($intervento).') ';
		} catch (RedBeanPHP\RedException\SQL $e) {
			$msg=$e->getMessage();
		}
	endif;	
	
	if (!empty($_REQUEST['del'])) : 
		$intervento=R::load('interventi', intval($_REQUEST['del']));
		try{
			R::trash($intervento);
		} catch (RedBeanPHP\RedException\SQL $e) {
			$msg=$e->getMessage();
		}
	endif;
	
	$interventi=R::find('interventi', 'dataintervento BETWEEN "'.$dal.'" AND "'.$al.'" ORDER by id ASC LIMIT 999');
	$pc=R::findAll('pc');
	$new=!empty($_REQUEST['create']);
	
	$titolo=($new) ? 'Nuovo intervento' : 'Intervento n. '.$id;
	if (!$id) $titolo= 'Interventi effettuati';
?>

<h1>
	<a href="index.php">
		<?=$titolo?>
	</a>
	
</h1>
<?php if ($id || $new) : ?>
		<form method="post" action="?p=interventi">
			<?php if ($id) : ?>
				<input type="hidden" name="id" value="<?=$intervento->id?>" />
			<?php endif; ?>
			<label for="descrizione">
				Descrizione
			</label>
			<input name="descrizione"  value="<?=$intervento->descrizione?>" autofocus required  />

			<label for="dataintervento">
				Data
			</label>
			<input name="dataintervento"  value="<?=date('Y-m-d',strtotime($intervento->dataintervento))?>" type="date" />
			
			<label for="pc_id">
				PC
			</label>
			<select name="pc_id">
				<option />
				<?php foreach ($pc as $a) : ?>
					<option value="<?=$a->id?>" <?=($a->id==$id) ? 'selected' :'' ?> >
						<?=$a->sn?>
					</option>
				<?php endforeach; ?>
			</select>
			<label for="ore">
				ore
			</label>			
			<input name="ore"  value="<?=$intervento->ore?>" type="number" />

			<label for="dataintervento">
				Spesa
			</label>			
			<input name="spesa"  value="<?=$intervento->spesa?>" type="number" step="any" />			
			
			<button type="submit" tabindex="-1">
				Salva
			</button>
			
			<a href="?p=interventi" >
				Elenco
			</a>			
			
			<a href="?p=interventi&del=<?=$a['id']?>" tabindex="-1">
				Elimina
			</a>					
		</form>
<?php else : ?>

		<form method="post" action="?p=interventi">
			<label for="da">
				dal 
			</label>
			<input name="dal" type="date"  value="<?=$dal?>"   />
			<label for="a">
				al
			</label>
			<input name="al"  type="date" value="<?=$al?>"   />
				
			<button type="submit" tabindex="-1">
				Filtra
			</button>

		</form>
	<div class="tablecontainer">
		<table id="ema" class="table table-striped table-bordered responsive">
			<colgroup>
				<col style="width:150px" />
			</colgroup>
			<thead>
				<tr>
					<th>PC</th>
					<th>Data e ora</th>
					<th>Descrizione</th>
					<th>ore</th>
					<th>Spesa</th>
					<th style="width:60px;text-align:center">Modifica</th>
					<th style="width:60px;text-align:center">Cancella</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
				 <th colspan="4" style="text-align:right">Total:</th>
                    <th></th>
				</tr>
			  </tfoot>
			<tbody>
			<?php foreach ($interventi as $r) : ?>
				<tr>
					<td>
							<?=($r->pc_id) ? $r->pc->sn : ''?>
					</td>			
					<td>
						<?=date('d/m/Y',strtotime($r->dataintervento))?>
					</td>
					<td>
						<?=$r->descrizione?>
					</td>
					<td style="text-align:right" >
						<?=$r->ore?>
					</td>	
					<td style="text-align:right" >
						<?=$r->spesa?>
					</td>				
					<td style="text-align:center" >
						<a href="?p=interventi&id=<?=$r['id']?>">
							Mod.
						</a>
					</td>
					<td style="text-align:center" >
						<a href="?p=interventi&del=<?=$r['id']?>" tabindex="-1">
							x
						</a>
					</td>							
				</tr>		
			<?php endforeach; ?>
			</tbody>
		</table>
		<h4 class="msg">
			<?=$msg?>
		</h4>	
	</div>
<?php endif; ?>
<a href="?p=interventi&create=1">Inserisci nuovo</a>
<script src="https://code.jquery.com/jquery-3.1.1.js"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.13/datatables.min.js"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
<script>
	var chg=function(e){
		console.log(e.name,e.value)
		document.forms.frm.elements[e.name].value=(e.value) ? e.value : null
	}
	
$(document).ready(function() {
//DATATABLE
//metto alla variabile otable la mia tabella che ho creato
$('#ema').dataTable({
 "footerCallback": function (row, data, start, end, display) {
                var api = this.api(), data;
                // Remove the formatting to get integer data for summation
                var intVal = function (i) {
                    return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '') * 1 :
                            typeof i === 'number' ?
                            i : 0;
                };
                // Total over all pages
               	total = api
                        .column(4)
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                // Total over this page
                pageTotal = api
                        .column(4, {page: 'current'})
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                // Update footer
                $(api.column(4).footer()).html(
                        '€' + pageTotal + 'Totale della pagina ( €' + total + ' Totale Generale)'
                        );
            }
	});});
		
</script>