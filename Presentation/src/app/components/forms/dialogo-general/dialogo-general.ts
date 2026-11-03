import { Component, inject } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MAT_DIALOG_DATA, MatDialogModule } from '@angular/material/dialog';
import { MatIconModule } from '@angular/material/icon';

@Component({
  imports: [MatDialogModule, MatButtonModule, MatIconModule],
  selector: 'app-dialogo-general',
  styleUrl: './dialogo-general.css',
  templateUrl: './dialogo-general.html',
})
export class DialogoGeneral {
  readonly data = inject<any>(MAT_DIALOG_DATA);
}
