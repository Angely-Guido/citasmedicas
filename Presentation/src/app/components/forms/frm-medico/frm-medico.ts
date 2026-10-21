import { Component, inject, OnInit, signal, effect} from '@angular/core';
import { MAT_DIALOG_DATA, MatDialog, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatDividerModule } from '@angular/material/divider';
import { MatAnchor, MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { form, FormField, FormRoot, maxLength, minLength, pattern, required, RootFieldContext, SchemaPath, validate } from '@angular/forms/signals';
import { MedicoCreateInput } from '../../../shared/models/medico-model';



function matchPattern(field : SchemaPath<string>, pattern: RegExp, message: string, optional = false) {
  return validate(field, (context: RootFieldContext<string>): { kind: string; message: string } | null => {
    const value = context.value();
    return (optional && !value) || pattern.test(value) ? null : {kind: 'pattern', message};
  });
}

@Component({
  imports: [MatDialogModule, MatDividerModule, MatAnchor, MatButtonModule, MatFormFieldModule, MatInputModule,
    FormRoot, FormField
  ],
  selector: 'app-frm-medico',
  styleUrl: './frm-medico.css',
  templateUrl: './frm-medico.html',
})
export class FrmMedico {
  titulo: string = "Formulario Medico";
  dialogRef = inject(MatDialogRef<FrmMedico>)
  private readonly dialog = inject(MatDialog);
  private readonly data   = inject(MAT_DIALOG_DATA);

  objMedico = signal<MedicoCreateInput>({
    especialidad_id: 0,
    nombre_completo: '',
    telefono: '',
    username: '',
    licencia: '',
    password: ''
  });

  frmMedico = form(this.objMedico, (s) => {
    //required(s.especialidad_id, {message: "Especialidad es requirida"});
    validate(s.especialidad_id, (context: RootFieldContext<number>): { kind: string; message: string } | null => {
      const value = context.value();
      //validar si el valor o su representación númerica es un entero
      const numericValue = Number(value); 
      if (isNaN(numericValue) || !Number.isInteger(numericValue) || numericValue <= 0) {
        return { kind: 'invalid', message: 'Especialidad inválida' };
      }
      return null;
    });


    required(s.username!, {message: "El nombre de usuario es requerido"});
    minLength(s.username!, 8, {message: "El usuario debe tener al menos 8 caracteres"});
    maxLength(s.username!, 16, {message: "El usuario no puede tener mas de 16 caracteres"});
    matchPattern(s.username!, /^[a-z0-9]{8,16}$/, "Formato usuario invalido");


    required(s.password!, {message: "contraseña es requerido"});
    minLength(s.password!, 8, {message: "La contraseña debe tener al menos 8 caracteres"});
    maxLength(s.password!, 64, {message: "La contraseña no puede tener mas de 64 caracteres"});
    matchPattern(s.password!, /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[#$@*])[\s\S]{8,16}$/, "Formato usuario invalido");

    required(s.nombre_completo, {message: "El nombre es requirida"});
    minLength(s.nombre_completo, 6, {message: "El nombre debe tener al menos 11 caracteres"});
    maxLength(s.nombre_completo, 150, {message: "El nombre no puede tener mas de 150 caracteres"});
    matchPattern(s.nombre_completo, /^[A-Za-zÑñÁÉÍÓÚáéíóú]{2,}(?: [A-Za-zÑñÁÉÍÓÚáéíóú]{2,}){1,3}$/u, "Formato nombre invalido");


    pattern(s.telefono!, /^[2-9][0-9]{3}-[0-9 ]{4}$/, { message: 'formato de telefono invalido' });


    required(s.licencia, {message: "La licencia es requirida"});
    minLength(s.licencia, 8, {message: "La licencia debe tener al menos 8 caracteres"});
    maxLength(s.licencia, 50, {message: "La licencia no puede tener mas de 50 caracteres"});

  }, {
    submission: {
      action: async () => {
        //console.log(this.objMedico());
        this.dialogRef.close(this.objMedico());
      }
    }
  }
);
  
  ngOnInit(): void {
    this.titulo = this.data.this;
  }


  constructor() {
  effect(() => {
    console.log({
      especialidad: this.frmMedico.especialidad_id().errors(),
      nombre: this.frmMedico.nombre_completo().errors(),
      licencia: this.frmMedico.licencia().errors(),
      telefono: this.frmMedico.telefono!().errors(),
      username: this.frmMedico.username!().errors(),
      password: this.frmMedico.password!().errors(),
    });
  });
}

}