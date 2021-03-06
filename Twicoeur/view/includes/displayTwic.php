<ul class="twicList" style="max-width: 25rem; list-style:none;">
  <?php if(count($context->twicsList) == 0):?>
  <li>
    <div class="alert alert-dismissible alert-info">
      Aucun post.
    </div>
  </li>
  <?php endif;
for ($i=0;$i<count($context->twicsList);$i++) :?>
  <?php
  $data['twic_id'] = $context->twicsList[$i]->id;
  $data['votes'] = $context->twicsList[$i]->nbvotes;

  $data['parent_id']= utilisateurTable::getUserById($context->twicsList[$i]->parent)->id;
  $data['parent_pic'] = htmlspecialchars(utilisateurTable::getUserById($context->twicsList[$i]->parent)->avatar);
  $data['parent_pic']= $data['parent_pic']==""?"https://pedago02a.univ-avignon.fr/~uapv1602171/squelette/images/avatar/user.svg":$data['parent_pic'];
  $data['parent_name'] = htmlspecialchars(utilisateurTable::getUserById($context->twicsList[$i]->parent)->identifiant);

  $data['emitter_id']= utilisateurTable::getUserById($context->twicsList[$i]->emetteur)->id;
  $data['emitter_pic']= htmlspecialchars(utilisateurTable::getUserById($context->twicsList[$i]->emetteur)->avatar);
  $data['emitter_pic']= $data['emitter_pic']==""?"https://pedago02a.univ-avignon.fr/~uapv1602171/squelette/images/avatar/user.svg":$data['emitter_pic'];
  $data['emitter_name'] = htmlspecialchars(utilisateurTable::getUserById($context->twicsList[$i]->emetteur)->identifiant);

  $data['post_pic'] = htmlspecialchars(postTable::getPostById($context->twicsList[$i]->post)->image);
  $data['post_text'] = postTable::getPostById($context->twicsList[$i]->post)->texte;
  $data['post_date'] = postTable::getPostById($context->twicsList[$i]->post)->date;

  $data['voted']= voteTable::getVoteByUserAndTweet($context->getSessionAttribute('num'),$data['twic_id']);
  $data['shared']= twicTable::alreadySharedTwic($context->getSessionAttribute('num'),$context->twicsList[$i]->post);
  $data['self_shared'] = $context->twicsList[$i]->parent == $context->getSessionAttribute('num');
?>

    <li>
      <div class="twic">
        <div class="votes <?php echo $data['votes'] == 0? '': " active ";?>">
          <span class="votes-number"><?php echo $data['votes'];?></span>

          <img src="images/svg/like.svg" width="15px" alt="vote">
        </div>
        <div class="card text-white bg-info mb-3">
          <div class="card-header">
            <h5 class="card-title">
          Posté par
          <a style="color: inherit;" href="?action=showAccount&id=<?php echo $data['parent_id']?>">
            <span data-toggle='tooltip'
            title='<img width="20px" src="<?php echo $data['parent_pic']?>"> <?php echo $data['parent_name'] ?>'>
              <?php echo '@'.$data['parent_name'] ?>
            </span>
          </a>
        </h5>
            <h6 class="card-subtitle text-muted">
          Partagé par
          <a style="color: inherit;" href="?action=showAccount&id=<?php echo $data['emitter_id']?>">
            <span data-toggle='tooltip' title='<img width="20px" src="<?php echo $data['emitter_pic'] ?>"> <?php echo $data['emitter_name']?>'>
              <?php echo '@'.$data['emitter_name'] ?>
            </span>
          </a>
        </h6>
          </div>

          <?php if($data['post_pic']!=""): ?>
          <img style="height: 200px; width: 100%; display: block;" src="<?php echo $data['post_pic'] ?>">
          <?php endif; ?>

          <div class="card-body">
            <p class="card-text">
              <?php echo $data['post_text'] ?>
            </p>
            <ul class="list-group list-group-flush" style="color:#222;">
              <li class="list-group-item">Date de création:
                <?php echo $data['post_date']?>
              </li>
              <li class="list-group-item more">
                <ul>
                  <li class="vote-item" onclick="vote(this)" data-twic="<?php echo $data['twic_id'] ?>">
                    <span><?php echo !$data['voted']?"Vote":"Enlever le vote";?></span>
                    <img src="images/svg/<?php echo !$data['voted']?"like":"unlike";?>.svg" alt="vote">
                  </li>
                  <li class="share-item"  <?php if($data[ 'shared']||$data[ 'self_shared']):?>style="cursor:not-allowed;"
                    <?php endif;?>>
                    <span <?php if(!$data[ 'shared'] &&!$data[ 'self_shared']):?>onclick="share(this,<?php echo $data['twic_id']?>)"<?php endif;?>>
                  <span><?php if(!$data['shared'] &&!$data['self_shared'])
                  echo "Partager";
                  elseif($data['shared'])
                  echo "Déjà partagé";
                  else
                  echo "Un de vos posts";
                  ?></span>
                  <img src="images/svg/share.svg" alt="share">
                </span>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </li>
    <?php endfor;?>
</ul>

<script type="text/javascript">
  let vote = (item) => {
    let id = item.dataset.twic

    let vote_elem = item.parentElement.parentElement.parentElement.parentElement.parentElement.parentElement.children[0];
    loadVote(id, vote_elem, item)
  }

  let share = (item,id) => {
    loadShare(id, item)
  }

  function loadShare(id, share_item) {
    $.ajax({
      url: "?action=shareTwic&id=" + id,
      success: function(result) {
          share_item.style.cursor = "not-allowed"
          share_item.children[0].children[0].innerText="Déjà partagé"
      }
    });
  }

  function getScrollPercent() {
    var h = document.documentElement,
      b = document.body,
      st = 'scrollTop',
      sh = 'scrollHeight';
    return (h[st] || b[st]) / ((h[sh] || b[sh]) - h.clientHeight) * 100;
  }



  document.addEventListener("scroll", () => {
    if (getScrollPercent() > 85) {
      if (lock)
        return
      lock = true
      offset += 5
      $.ajax({
        url: "?action=infFlux&offset=" + offset,
        success: (result) => {
          createTwics(JSON.parse(result))
          lock = false
        }
      })
    }

  });



  function loadVote(id, item, vote_item) {
    $.ajax({
      url: "?action=voteTwic&id=" + id,
      success: (result) => {
        res = parseInt(result)

        if (res >= 1) {
          if (!item.classList.contains('active'))
            item.classList.add('active')

          if (parseInt(item.children[0].innerHTML) - res < 1) {
            vote_item.children[0].textContent = "Enlever le vote"
            vote_item.children[1].setAttribute('src', 'images/svg/unlike.svg')
          } else {
            vote_item.children[0].textContent = "Vote"
            vote_item.children[1].setAttribute('src', 'images/svg/like.svg')
          }



          item.children[0].innerHTML = res;
          item.classList.add('tada')
          setTimeout(() => {
            item.classList.remove('tada')
          }, 1000);

        } else {
          console.log('d');
          item.classList.remove('active');
          item.children[0].innerHTML = 0;
          vote_item.children[0].textContent = "Vote"
          vote_item.children[1].setAttribute('src', 'images/svg/like.svg')
        }
      }
    });
  }
</script>
