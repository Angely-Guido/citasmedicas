import { Component, effect, inject, OnInit, signal } from '@angular/core';
import { MatTableModule } from '@angular/material/table';
import { MedicoService } from '../../shared/services/medico-service';
import { MedicoModel } from '../../shared/models/medico-model';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { FrmMedico } from '../forms/frm-medico/frm-medico';

@Component({
  imports: [MatTableModule, MatCardModule, MatButtonModule, MatIconModule,
    MatDialogModule
  ],
  selector: 'app-medico-component',
  styleUrl: './medico-component.css',
  templateUrl: './medico-component.html',
})


export class MedicoComponent implements OnInit {
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

  constructor() {
    effect(() => {
      const respuesta = this.srvMedico.medicos.value();
      if (respuesta !== undefined){
        //console.table(respuesta.data);
        this.dataSource.set(respuesta.data);
      }
    });
  }

  ngOnInit(): void {
    this.srvMedico.filtrar( {
      nombre       : '',
      cedula       : '',
      especialidad : ''
    })
  }

  public mostrarDialogo(titulo: string, datos?: MedicoModel, info? : boolean){
    const dialogRef = this.dialog.open(FrmMedico, {
      data: {
        width: '50vw',
        maxwidth: '35rem',
        title: titulo,
        data: datos,
        info: info
      },
      disableClose: true
    });
    dialogRef.afterClosed()
      .subscribe((objMedico) => {
        if(objMedico === undefined){
          return;
        }
        console.log('Medico creado', objMedico);
        this.srvMedico.guardar(objMedico)
        .subscribe({
          complete :() => {
            alert('datos guardados');
          }
        })
      });
  } //Fin de mostrarDialogo

  onResetPassword(id: number): void {

  }

  onDelete(id: number): void {

  }

  onEdit(id: number): void {

  }

  onInfo(id: number): void {
    
  }

  onCreate(): void {
    this.mostrarDialogo('nuevo cliente');
  }
}