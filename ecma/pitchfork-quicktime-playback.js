var loaded_items = new Array();

function generate_quicktime_snippet(hash, style)
{
	// Fetch the embed span
	var span = document.getElementById('embed-'+hash);
	
	// Remove any currently playing snippet
	remove_quicktime_snippets();
	
	// Save the loaded item so we can deactivate it later
	loaded_items[0] = hash;
	
	if(!span) displayWorking("PLAYBACK_ERR00");
	
	if(style == true) span.style.display = "block";
	
	// Delete the current image being displayed
	span.firstChild.style.display = "none";
	
	embed_Object = generate_quicktime_embedObject(hash, style, 'true');
	
	span.appendChild(embed_Object);
}

function generate_quicktime_embedObject(hash, style, autoplay)
{
	embed_Object = document.createElement("object");
	embed_Object.setAttribute("classid", "clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B");
	embed_Object.setAttribute("codebase","http://www.apple.com/qtactivex/qtplugin.cab");
	
	embed_ParamSrc = document.createElement("param");
	embed_ParamSrc.setAttribute("name","src");
	embed_ParamSrc.setAttribute("value","pitchfork-application-download.php?mode=stream&item="+hash);
	embed_Object.appendChild(embed_ParamSrc);
	
	embed_ParamPlay = document.createElement("param");
	embed_ParamPlay.setAttribute("name","autoplay");
	embed_ParamPlay.setAttribute("value",autoplay);
	embed_Object.appendChild(embed_ParamPlay);
	
	embed_Embed = document.createElement("embed");
	embed_Embed.setAttribute("autoplay",autoplay);
	embed_Embed.setAttribute("id","embed-Object-"+hash);
	embed_Embed.setAttribute("value","pitchfork-application-download.php?mode=stream&item="+hash);
		if(style == 'true') {embed_Embed.setAttribute("height","272"); embed_Embed.setAttribute("width","484");}
		else {embed_Embed.setAttribute("height","18"); embed_Embed.setAttribute("width","484");}
	embed_Object.appendChild(embed_Embed);
	return embed_Object;
}

function generate_multiple_snippets(hashes, styles)
{
	// Remove any currently playing snippet
	remove_quicktime_snippets();
}

function remove_quicktime_snippets()
{
	for(index in loaded_items)
	{
		var span = document.getElementById('embed-'+loaded_items[index]); //alert('embed-'+loaded_items[index]);
		if(!span) 
		{
			displayWorking("PLAYBACK_ERR01");
			return;
		}
		
		for(i=0; i<span.childNodes.length; i++)
		{
			//span.removeChild(span.childNodes[i]);
			if(span.childNodes[i].nodeName != "IMG") span.removeChild(span.childNodes[i]);
		}
		span.firstChild.style.display = "inline";
	}
	loaded_item = "";
}