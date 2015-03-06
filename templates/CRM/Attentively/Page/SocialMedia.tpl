<style>

.user-avatar, .network-container {ldelim}
    background-color: #fff;
    border: 1px solid #a8a8a8;
    border-radius: 50%;
    height: 80px;
    overflow: hidden;
    padding: 4px;
    width: 80px;
{rdelim}

.gravatar-image, .network-image {ldelim}
    border-radius: 50%;
{rdelim}

.klout-label {ldelim}
    margin-top: -15px;
{rdelim}

.klout-image {ldelim}
    position: relative;
{rdelim}

.klout-content {ldelim}
    margin-top: -48px;
    margin-left: -5px;
    padding: 13px;
    font-size: 21px;
    font-weight: bold; 
    color: #fff;
{rdelim}

.display {ldelim}
    border: 0px none;
{rdelim}

.attentively-branding {ldelim}
    padding: 5px;
{rdelim}

.att-logo {ldelim}
    height: 23px;
    margin-left: 5px;
    position: absolute;
{rdelim}
</style>
<div class="attentively-branding">
<a href="{$attURL}">Manage contact in <img class="att-logo" src="{$config->extensionsURL}/biz.jmaconsulting.attentively/images/Attentively.png"></a>
</div>
{strip} 
    <div style="margin-top:2px;">
      <h3>Klout Score</h3>
      <table id="media" class="display"><tr>
      {if $networkData.gravatar and !$klout}
      <td><div class="user-avatar">
        <a href="{$networkData.gravatar.url}">{$networkData.gravatar.image}</a>
      </div></td>
      {/if}
      {if $klout}
      <td><div class="user-avatar">
        <a href="{$networkData.gravatar.url}">{$networkData.gravatar.image}</a>
      </div>
    <div class="klout-label" class="klout-image"><img width="40px" src="{$config->extensionsURL}/biz.jmaconsulting.attentively/images/klout.png"/></div><div class="klout-content">{$klout}</div>
{else}
    <div id="help">{ts}Klout score is unavailable{/ts}</div>
{/if}
     </td>
      </tr>
      </table>
<h3>Networks</h3>
{if $networkData}
<table id="networks" class="display">
  <tbody>
     <tr>
  {foreach from=$networkData item=url key=name}
    {if $name neq 'gravatar'}
      <td style="padding-top:14px; border:none;"><div class="network-container"><a href="{$url.url}">{$url.image}</a></div></td>
    {/if}
  {/foreach}
     </tr>
  </tbody>
</table>
{else}
	<div id="help">{ts}No networks were found{/ts}</div>
{/if}
<h3>Posts</h3>
{if $posts}
<table id="posts" class="display">
  <thead> 
    <tr>
      <th>{ts}Posts{/ts}</th>
    </tr>
  </thead>
  <tbody>
  {foreach from=$posts item=post key=name}
     <tr><td class="bold" style="padding-top:14px;">{$post}</td></tr>
  {/foreach}
  </tbody>
</table>
{else}
	<div id="help">{ts}No posts were found{/ts}</div>
{/if}
{/strip}
