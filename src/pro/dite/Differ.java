package pro.dite;

import org.eclipse.jgit.diff.*;
import org.eclipse.jgit.lib.*;
import org.eclipse.jgit.revwalk.RevTree;
import org.eclipse.jgit.revwalk.RevWalk;
import org.eclipse.jgit.treewalk.CanonicalTreeParser;
import org.eclipse.jgit.treewalk.TreeWalk;
import org.eclipse.jgit.treewalk.filter.TreeFilter;

import java.io.IOException;
import java.util.ArrayList;
import java.util.List;

class Differ
{

    private final ObjectReader reader;

    public Differ(Repository repo)
    {
        this.reader = repo.newObjectReader();
    }

    public List<ObjectId> getFiles(RevTree b) throws IOException
    {
        TreeWalk tw = new TreeWalk(reader);
        tw.addTree(b);
        tw.setRecursive(true);
        List<ObjectId> list = new ArrayList<ObjectId>();
        while (tw.next())
        {
            String path = tw.getPathString();
            if (path.toLowerCase().contains("vendor/")
                || !path.toLowerCase().endsWith(".php"))
            {
                // TODO allow other extensions as well?
                // TODO refactor with headwalker
                continue;
            }

            list.add(tw.getObjectId(0));
        }
        return list;
    }

    public List<DiffEntry> getEdits(AnyObjectId a, AnyObjectId b) throws IOException
    {
        RevWalk rw = new RevWalk(reader);

        CanonicalTreeParser aParser = new CanonicalTreeParser();
        CanonicalTreeParser bParser = new CanonicalTreeParser();
        aParser.reset(reader, rw.parseTree(a));
        bParser.reset(reader, rw.parseTree(b));

        TreeWalk walk = new TreeWalk(reader);
        walk.addTree(aParser);
        walk.addTree(bParser);
        walk.setRecursive(true);
        walk.setFilter(TreeFilter.ANY_DIFF);

        return DiffEntry.scan(walk);
    }

}
