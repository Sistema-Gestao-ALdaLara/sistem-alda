@extends('home.app')
@section('content')
            <!--************************************
    Home Slider Start
    tg-addmissionslider
  *************************************-->

  <div id="tg-homeslider"
  class="tg-homeslider tg-homeslidervthree tg-homeslidervfour owl-carousel tg-btnround tg-haslayout">
  <div class="item">
      <figure>
          <img src="{{asset('assets/images/5.JPG')}}" alt="image description">
          <figcaption class="tg-slidercontent">
              <div class="tg-slidercontentbox">
                  <div class="container">
                      <div class="row">
                          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                              <h1>Oferecendo!<span>Melhores qualidades de ensino</span></h1>
                          </div>
                      </div>
                  </div>
              </div>
          </figcaption>
      </figure>
  </div>
  <div class="item">
      <figure>
          <img src="{{asset('assets/images/8.JPG')}}" alt="image description">
          <figcaption class="tg-slidercontent">
              <div class="tg-slidercontentbox">
                  <div class="container">
                      <div class="row">
                          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                              <h1>Nossa Visão e Missão</h1>
                              <a class="tg-btn" href="nossamissaoevisao">ler mais</a>
                          </div>
                      </div>
                  </div>
              </div>
          </figcaption>
      </figure>
  </div>
  <div class="item">
      <figure>
          <img src="{{asset('assets/images/4.JPG')}}" alt="image description">
          <figcaption class="tg-slidercontent">
              <div class="tg-slidercontentbox">
                  <div class="container">
                      <div class="row">
                          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                              <h1>Nossa Identidade</h1>
                              <a class="tg-btn" href="nossaidentidade">ler mais</a>
                          </div>
                      </div>
                  </div>
              </div>
          </figcaption>
      </figure>
  </div>
</div>
<div class="tg-tickerbox">
  <div class="container">
      <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
              <span>Destaque:</span>
              <div id="tg-ticker" class="tg-ticker owl-carousel">
                  <div class="item">
                      <div class="tg-description">
                          <p><a href="noticia?id=5">Educação quer professores do Simione Mucune nas salas de
                                  aulas</a></p>
                      </div>
                  </div>
                  <div class="item">
                      <div class="tg-description">
                          <p><a href="noticia?id=4">Palestra 4</a></p>
                      </div>
                  </div>
                  <div class="item">
                      <div class="tg-description">
                          <p><a href="noticia?id=3">Palestra 2</a></p>
                      </div>
                  </div>
                  <div class="item">
                      <div class="tg-description">
                          <p><a href="noticia?id=2">Projectos Finais</a></p>
                      </div>
                  </div>
                  <div class="item">
                      <div class="tg-description">
                          <p><a href="noticia?id=1">Feira da Ciencia</a></p>
                      </div>
                  </div>

              </div>
          </div>
      </div>
  </div>
</div>
<!--************************************
Home Slider End
*************************************-->


<div class="container">

    <div class="row">
        <div id="tg-twocolumns" class="tg-twocolumns">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <section class="tg-sectionspace tg-haslayout">
                    <div class="tg-shortcode tg-welcomeandgreeting tg-welcomeandgreeting-v2">
                        <figure><img style="width: 164px; height: 201px; " src="{{asset('assets')}}/images/directorgeral.jpg"
                                alt="image description"></figure>
                        <div class="tg-shortcodetextbox">
                            <h2>Bem-vindo & saudações!</h2>
                            <div class="tg-description">
                                <p>Dou as boas vindas a toda a comunidade educativa: alunos, pais,
                                    professores e assistentes operacionais e técnicos, certos de que o
                                    caminho para o sucesso exige muito empenho, persistência e dedicação...
                                </p>
                            </div>
                            <span class="tg-name">Silvestre Augusto Francisco</span>
                            <span class="tg-designation">Director Geral</span>
                            <div class="tg-btnpluslogo">
                                <a class="tg-btn" href="mensagemdirector">saber mais</a>
                                <strong class="tg-universitylogo"><a href="javascript:void(0);"><img
                                            src="{{asset('assets/images/lgo.png" alt="image ')}}description"></a></strong>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-xs-12 col-sm-8 col-md-9 col-lg-9">
                <div id="tg-content" class="tg-content">
                    <section class="tg-sectionspace tg-haslayout">
                        <div class="tg-borderheading">
                            <h2>Últimos Eventos</h2>
                        </div>

                        <div class="tg-events">
                            <div class="row">



                                <div class="col-xs-6 col-sm-6 col-md-4 col-lg-4">
                                    <article class="tg-themepost tg-eventpost">
                                        <figure class="tg-featuredimg">
                                            <a href="evento?id=6">
                                                <img src="fotos/a61f0154262a8581408bc9cdd49882b2.jpg"
                                                    style="height: 165.75px; object-fit: cover;"
                                                    alt="image description">
                                            </a>
                                        </figure>
                                        <div class="tg-themepostcontent">
                                            <ul class="tg-matadata">
                                                <li>
                                                    <a href="evento?id=6">
                                                        <i class="fa fa-calendar"></i>
                                                        <span>11 Fev 2021</span>
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tg-themeposttitle">
                                                <h3><a href="evento?id=6">Provas 2546</a></h3>
                                            </div>
                                            <div class="tg-description">
                                                <p>Provas 2 bkjhcbujwekhfbejk2fdk-len
                                                    BKJGBhjgvdh...<a href="evento?id=6"> Ler Mais</a></p>
                                            </div>
                                        </div>
                                    </article>
                                </div>

                                <div class="col-xs-6 col-sm-6 col-md-4 col-lg-4">
                                    <article class="tg-themepost tg-eventpost">
                                        <figure class="tg-featuredimg">
                                            <a href="evento?id=4">
                                                <img src="fotos/4cb340884dc4ffa6eb28c3455551aa63.jpg"
                                                    style="height: 165.75px; object-fit: cover;"
                                                    alt="image description">
                                            </a>
                                        </figure>
                                        <div class="tg-themepostcontent">
                                            <ul class="tg-matadata">
                                                <li>
                                                    <a href="evento?id=4">
                                                        <i class="fa fa-calendar"></i>
                                                        <span>16 Jan 2021</span>
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tg-themeposttitle">
                                                <h3><a href="evento?id=4">Próximas Provas</a></h3>
                                            </div>
                                            <div class="tg-description">
                                                <p>sdfajkfbdsajkfbsadjkfndsjkmf<a href="evento?id=4"> Ler
                                                        Mais</a></p>
                                            </div>
                                        </div>
                                    </article>
                                </div>

                                <div class="col-xs-6 col-sm-6 col-md-4 col-lg-4">
                                    <article class="tg-themepost tg-eventpost">
                                        <figure class="tg-featuredimg">
                                            <a href="evento?id=1">
                                                <img src="fotos/1ca16ecc24db6a4c3996d329230068b8.jpg"
                                                    style="height: 165.75px; object-fit: cover;"
                                                    alt="image description">
                                            </a>
                                        </figure>
                                        <div class="tg-themepostcontent">
                                            <ul class="tg-matadata">
                                                <li>
                                                    <a href="evento?id=1">
                                                        <i class="fa fa-calendar"></i>
                                                        <span>12 Dez 2020</span>
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tg-themeposttitle">
                                                <h3><a href="evento?id=1">Reuniao com encarregados</a></h3>
                                            </div>
                                            <div class="tg-description">
                                                <p>gfvvchkdsavcsdvhcgsdhvcdsgvcdsghcvdsghvcdsgh<a
                                                        href="evento?id=1"> Ler Mais</a></p>
                                            </div>
                                        </div>
                                    </article>
                                </div>


                            </div>
                        </div>
                    </section>
                    <section class="tg-sectionspace tg-haslayout">
                        <div class="tg-latestnews">
                            <div class="tg-borderheading">
                                <h2>Últimas Notícias</h2>
                            </div>
                            <div id="tg-latestnewsslider"
                                class="tg-latestnewsslider owl-carousel tg-posts">


                                <div class="item">
                                    <article class="tg-themepost tg-newspost">
                                        <figure class="tg-featuredimg">
                                            <a href="noticia?id=5">
                                                <img src="fotos/15586846917baf1674c376aff28ef333.jpg"
                                                    style="height:165px; object-fit: cover;"
                                                    alt="image description">
                                            </a>
                                        </figure>
                                        <div class="tg-themepostcontent">
                                            <ul class="tg-matadata">
                                                <li>
                                                    <a href="noticia?id=5">
                                                        <i class="fa fa-calendar"></i>
                                                        <span>15 Abr 2021</span>
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tg-themeposttitle">
                                                <h3><a href="noticia?id=5">Educação quer professores do
                                                        Simione Mucune nas salas de aulas</a></h3>
                                            </div>
                                            <div class="tg-description">
                                                <p>
                                                <div>fgjkfhgjkfdgbjhjhjhjhjhjhjhjhjhjhfgvbsdhvb...<a
                                                        href="noticia?id=5"> Ler Mais</a></p>
                                                </div>
                                            </div>
                                    </article>
                                </div>

                                <div class="item">
                                    <article class="tg-themepost tg-newspost">
                                        <figure class="tg-featuredimg">
                                            <a href="noticia?id=4">
                                                <img src="fotos/56bf21534be8b5299bb6f5b80376a2c0.jpg"
                                                    style="height:165px; object-fit: cover;"
                                                    alt="image description">
                                            </a>
                                        </figure>
                                        <div class="tg-themepostcontent">
                                            <ul class="tg-matadata">
                                                <li>
                                                    <a href="noticia?id=4">
                                                        <i class="fa fa-calendar"></i>
                                                        <span>06 Out 2020</span>
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tg-themeposttitle">
                                                <h3><a href="noticia?id=4">Palestra 4</a></h3>
                                            </div>
                                            <div class="tg-description">
                                                <p>Lorem ipsum dolor sit amet, consectetur adipisi...<a
                                                        href="noticia?id=4"> Ler Mais</a></p>
                                            </div>
                                        </div>
                                    </article>
                                </div>

                                <div class="item">
                                    <article class="tg-themepost tg-newspost">
                                        <figure class="tg-featuredimg">
                                            <a href="noticia?id=3">
                                                <img src="fotos/2ea26a9ca3d226a436f2c881d4de1f37.jpg"
                                                    style="height:165px; object-fit: cover;"
                                                    alt="image description">
                                            </a>
                                        </figure>
                                        <div class="tg-themepostcontent">
                                            <ul class="tg-matadata">
                                                <li>
                                                    <a href="noticia?id=3">
                                                        <i class="fa fa-calendar"></i>
                                                        <span>06 Out 2020</span>
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tg-themeposttitle">
                                                <h3><a href="noticia?id=3">Palestra 2</a></h3>
                                            </div>
                                            <div class="tg-description">
                                                <p>Olá Mundo!<br><a href="noticia?id=3"> Ler Mais</a></p>
                                            </div>
                                        </div>
                                    </article>
                                </div>

                                <div class="item">
                                    <article class="tg-themepost tg-newspost">
                                        <figure class="tg-featuredimg">
                                            <a href="noticia?id=2">
                                                <img src="fotos/56bf21534be8b5299bb6f5b80376a2c0.jpg"
                                                    style="height:165px; object-fit: cover;"
                                                    alt="image description">
                                            </a>
                                        </figure>
                                        <div class="tg-themepostcontent">
                                            <ul class="tg-matadata">
                                                <li>
                                                    <a href="noticia?id=2">
                                                        <i class="fa fa-calendar"></i>
                                                        <span>04 Jan 2021</span>
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tg-themeposttitle">
                                                <h3><a href="noticia?id=2">Projectos Finais</a></h3>
                                            </div>
                                            <div class="tg-description">
                                                <p>csdgvfdhgnfgjmnyhthm<a href="noticia?id=2"> Ler Mais</a>
                                                </p>
                                            </div>
                                        </div>
                                    </article>
                                </div>

                                <div class="item">
                                    <article class="tg-themepost tg-newspost">
                                        <figure class="tg-featuredimg">
                                            <a href="noticia?id=1">
                                                <img src="fotos/1ca16ecc24db6a4c3996d329230068b8.jpg"
                                                    style="height:165px; object-fit: cover;"
                                                    alt="image description">
                                            </a>
                                        </figure>
                                        <div class="tg-themepostcontent">
                                            <ul class="tg-matadata">
                                                <li>
                                                    <a href="noticia?id=1">
                                                        <i class="fa fa-calendar"></i>
                                                        <span>01 Jan 1970</span>
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tg-themeposttitle">
                                                <h3><a href="noticia?id=1">Feira da Ciencia</a></h3>
                                            </div>
                                            <div class="tg-description">
                                                <p>csdgvfdhgnfgjmnyhthm<a href="noticia?id=1"> Ler Mais</a>
                                                </p>
                                            </div>
                                        </div>
                                    </article>
                                </div>


                            </div>
                            <div class="tg-btnsbox">
                                <a class="tg-btn" href="noticias">ver todas notícias</a>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <div class="col-xs-12 col-sm-4 col-md-3 col-lg-3">
                <aside id="tg-sidebar" class="tg-sidebar">
                    <div class="tg-widget tg-widgetsearchcourse">
                        <div class="tg-widgettitle">
                            <h3>Pesquisar Curso</h3>
                        </div>
                        <div class="tg-widgetcontent">
                            <form action="./cursos" method="get" class="tg-formtheme tg-formsearchcourse">
                                <fieldset>
                                    <div class="tg-inputwithicon">
                                        <i class="icon-book"></i>
                                        <input type="text" name="query" class="form-control"
                                            placeholder="Palavra-chave">
                                    </div>
                                    <button type="submit" class="tg-btn">pesquisar agora</button>
                                    <a href="cursos">Ver Todos os Cursos</a>
                                </fieldset>
                            </form>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</div>


@push('js')
    <script>
        $("#add-inscricao").submit(function(e){
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: "{{route('candidato.inscrever')}}",
                data: $("#add-inscricao").serialize(),
                dataType: "json",
                success: function (response) {
                    console.log(response)
                    if(response.status == 500){
                        ModalAlerta('error',response.register,3)
                    }
                    if(response.status == 200){
                        ModalAlerta('confirm',response.register,1)
                    }
                },
                error: function(error){
                    ModalAlerta('error',error,3)
                }
            });
        })
        
    </script>
@endpush
@endsection