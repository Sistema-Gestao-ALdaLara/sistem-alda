  <!-- Modal -->
  <div class="modal fade" id="exampleModalInscricao" tabindex="-1" role="dialog"
  aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
      <form method="get" id="confirmarInscricao" onsubmit="confirmarInscricao(this,event)" action="">
          <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
                  <h5 class="modal-title" id="exampleModalLabel">Verificar Inscrição</h5>
              </div>
              <div class="modal-body">
                  <div class="text-center">
                      <small>Introduz ID da Inscrição que lhe foi enviado por email.</small>
                      <input name="query" required
                          style="border: 1px solid #286090; border-radius: 5px; 5px 5px;" type="text">
                  </div>

              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                  <button type="submit" class="btn btn-primary">Pesquisar</button>
              </div>
          </div>
      </form>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModalMatricula" tabindex="-1" role="dialog"
  aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
      <form id="confirmarMatricula" onsubmit="ConfirmarMatricula(this, event);" method="get" action="">
          <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
                  <h5 class="modal-title" id="exampleModalLabel">Verificar Matrícula</h5>
              </div>
              <div class="modal-body">
                  <div class="text-center">
                      <small>Introduz Código de Confirmação que lhe foi enviado por email.</small>
                      <input min="1" name="id" required
                          style="border: 1px solid #286090; border-radius: 5px; 5px 5px;" type="number">
                      <div id="custom_error"></div>
                  </div>

              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                  <button type="submit" class="btn btn-primary">Pesquisar</button>
              </div>
          </div>
      </form>
  </div>
</div>
