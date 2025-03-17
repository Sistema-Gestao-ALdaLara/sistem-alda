
        function ModalAlerta(_type, _title, _tipo) {
          switch (_type) {
              case 'confirm':
                  Modal(_title, 1);
                  break;

              case 'alert':
                  Modal(_title, 2);
                  break;

              case 'error':
                  Modal(_title, 3);
                  break;

              case 'info':
                  Modal(_title, 4);
                  break;

              default:
                  break;
          }
      }

      function Modal(_title, _tipo) {
          if (_tipo == 1) {
              tipo_txt = 'success';
          } else if (_tipo == 2) {
              tipo_txt = 'warning';
          } else if (_tipo == 3) {
              tipo_txt = 'error';
          } else {
              tipo_txt = 'info';
          }

          if (_tipo == 1) {
              Swal.fire({
                  html: _title,
                  type: tipo_txt,
                  icon: tipo_txt,
                  confirmButtonText: 'Entendi'
              });
          } else {
              Swal.fire({
                  html: _title,
                  type: tipo_txt,
                  icon: tipo_txt,
                  confirmButtonText: 'Entendi'
              });
          }
      }

      function returnSexo(sexo){
          if(sexo == 'M'){
              return "Masculino"
          }
          return "Feminino"
      }