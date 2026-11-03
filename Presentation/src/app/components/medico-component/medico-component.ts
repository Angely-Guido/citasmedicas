import { Component, effect, inject, OnInit, signal } from '@angular/core';
import { MatTableModule } from '@angular/material/table';
import { MedicoService } from '../../shared/services/medico-service';
import { MedicoModel } from '../../shared/models/medico-model';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { FrmMedico } from '../forms/frm-medico/frm-medico';
import { DialogoGeneral } from '../forms/dialogo-general/dialogo-general';

@Component({
  imports: [MatTableModule, MatCardModule, MatButtonModule, MatIconModule,
    MatDialogModule
  ],
  selector: 'app-medico-component',
  styleUrl: './medico-component.css',
  templateUrl: './medico-component.html',
})


export class MedicoComponent implements OnInit {

  buscarID = signal<number | null>(null);

  displayedColumns: string[] = [
    'id',
    'nombre_completo', 
    'especialidad', 
    'licencia', 
    'telefono',
    'botones'];

  dataSource = signal<MedicoModel[]>([]);

  srvMedico = inject(MedicoService);

  dialog = inject(MatDialog);

  filtro :  { nombre: string; cedula: string; especialidad: string } = 
            {nombre: '', cedula: '', especialidad: ''};

  constructor() {
    effect(() => { //Para filtrar
      const respuesta = this.srvMedico.medicos.value();
      if (respuesta !== undefined){
        //console.table(respuesta.data);
        this.dataSource.set(respuesta.data);
      }
    });

    effect(() => { //Para buscar por Id
      const id = this.buscarID();
      const respuesta = this.srvMedico.medicoPorId;

      if (id === null) {
        return;
      }

      if (respuesta.status() === 'resolved') {
        const medico = respuesta.value()?.data;
/*         if (medico === null) {
         // this.medicoSeleccionado.set(null);
          alert('No se encontró el médico');
          return;
        }
        if (medico?.id !== id) {
          return;
        } */

        //this.medicoSeleccionado.set(medico);
        this.mostrarDialogo('Editar Médico', medico!, false, id);
      } else if (respuesta.status() === 'error') {
        console.error(`No se pudo buscar el médico con ID ${id}:`, respuesta.error());
        alert('No se pudo cargar el médico');
      }
    });
  }

  private resetFilter() : void {
    this.filtro = {nombre: '', cedula: '', especialidad: ''};
    this.filterMedicos();
    
  }

  private filterMedicos() {
    this.srvMedico.filtrar(this.filtro);
    
  }

  ngOnInit(): void {
    this.resetFilter();
  }

  private alerta(icono : string, mensaje : string, textoAceptar : string) {
    this.dialog.open(DialogoGeneral, {
      width: '30vw',
      maxWidth: '20rem',
      data: {
        icono: icono,
        texto: mensaje,
        textoAceptar: textoAceptar
      }
    });

  }

  public mostrarDialogo(titulo: string, datos?: MedicoModel, info? : boolean, id?: number){
    const dialogRef = this.dialog.open(FrmMedico, {
      width: '50vw',
      maxWidth: '35rem',
      data: {
        title: titulo,
        data: datos,
        info: info
      },
      disableClose: true
    });
    dialogRef.afterClosed()
      .subscribe((objMedico) => {
        if(objMedico === undefined || objMedico === false){
          return;
        }
        console.log('Medico creado', objMedico);
        this.srvMedico.guardar(objMedico, id)
        .subscribe({
          complete :() => {
            this.resetFilter();
            this.alerta('check', id === undefined ? 'Datos creados' : 'Datos Actualizados', ' Cerrar ')
            
            //this.srvMedico.medicos.reload();
          }
        })
      });
  } //Fin de mostrarDialogo

  onResetPassword(idMedico: number): void {

  }

  onDelete(idMedico: number): void {

  }

  onEdit(idMedico: number): void {
    this.buscarID.set(idMedico);
    this.srvMedico.buscarPorId(idMedico);
    
  }

  onInfo(idMedico: number): void {
    
  }

  onCreate(): void {
    this.mostrarDialogo('nuevo cliente');
  }
}