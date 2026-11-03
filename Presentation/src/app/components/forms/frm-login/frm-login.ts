import { Component, signal, inject } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormField, MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { FormField, FormRoot, form, required } from '@angular/forms/signals';
import { MatInput } from "@angular/material/input";
import { LoginForm } from '../../../shared/models/interfaces'; 
//import { AuthService } from '../../../shared/services/auth.service';
import { MatDialogRef } from '@angular/material/dialog';

@Component({
  selector: 'app-frm-login',
  imports: [MatCardModule, MatIconModule, MatButtonModule,
    MatFormFieldModule, MatFormField, FormRoot, MatInput, FormField],
  templateUrl: './frm-login.html',
  styleUrl: './frm-login.css',
})
export class FrmLogin {

  //private readonly srvAuth = inject(AuthService);

  dialogRef = inject(MatDialogRef<FrmLogin>)
  public mostrarError = signal(false);
  objLogin = signal<LoginForm>( {
    username : '',
    password: ''
  });

  frmLogin = form(this.objLogin, (s)=> {
    required(s.username, { message: 'Usuario es requerido'});
    required(s.password, { message: 'Contraseña es requerida'});
    
  },
  {
    submission: {
      action: async () => {
        //console.log("valor que contiene el objeto", this.objLogin());
        this.dialogRef.close(this.objLogin());
      }
    }
  }
);
}